<?php

use App\Services\Court\CourtBot;
use App\Services\Court\CourtSessionStore;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

const COURT_CHAT_ID = 555;
const GEMINI_GENERATE_URL = 'generativelanguage.googleapis.com/v1beta/models/gemini-test-flash:generateContent';

beforeEach(function () {
    config([
        'services.telegram.token' => 'test-token',
        'services.gemini.key' => 'gemini-key',
    ]);
});

/**
 * Fake Telegram and Gemini; Gemini answers every prompt with the given reply.
 */
function fakeCourtApis(mixed $geminiReply = null): void
{
    Http::preventStrayRequests();

    Http::fake([
        'api.telegram.org/bottest-token/getFile' => Http::response(['ok' => true, 'result' => ['file_path' => 'voice/file_1.oga']]),
        'api.telegram.org/file/bottest-token/voice/file_1.oga' => Http::response('OGG-BYTES'),
        'api.telegram.org/bottest-token/*' => Http::response(['ok' => true]),
        'generativelanguage.googleapis.com/v1beta/models' => Http::response([
            'models' => [['name' => 'models/gemini-test-flash', 'supportedGenerationMethods' => ['generateContent']]],
        ]),
        GEMINI_GENERATE_URL => $geminiReply instanceof Closure || $geminiReply === null
            ? ($geminiReply ?? Http::response([], 500))
            : Http::response(['candidates' => [['content' => ['parts' => [['text' => $geminiReply]]]]]]),
    ]);
}

function sendToCourtBot(array $message): void
{
    app(CourtBot::class)->handle(['chat' => ['id' => COURT_CHAT_ID], ...$message]);
}

function openCivilCase(): void
{
    sendToCourtBot(['text' => '🏛 Fuqarolik sudi']);
}

/**
 * @return list<string>
 */
function sentTelegramTexts(): array
{
    return collect(Http::recorded())
        ->map(fn (array $pair): Request => $pair[0])
        ->filter(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage'))
        ->map(fn (Request $request): string => $request['text'])
        ->values()
        ->all();
}

function courtSession(): ?array
{
    return app(CourtSessionStore::class)->get(COURT_CHAT_ID);
}

it('greets the user with the court menu on /start and closes any open hearing', function () {
    fakeCourtApis();
    openCivilCase();

    sendToCourtBot(['text' => '/start']);

    expect(courtSession())->toBeNull();
    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/sendMessage')
        && str_starts_with($request['text'], '⚖️ Assalomu alaykum!')
        && $request['reply_markup']['keyboard'] === [
            [['text' => '🏛 Fuqarolik sudi'], ['text' => '🚔 Jinoyat sudi']],
            [['text' => "📋 Ma'muriy sud"], ['text' => '💼 Iqtisodiy sud']],
            [['text' => '📈 Apellyatsiya shikoyati'], ['text' => '⚖️ Kassatsiya shikoyati']],
            [['text' => '🤝 Hakamlik sudi']],
        ]);
});

it('opens the chosen case and sends the case, the opponent and the judge', function () {
    fakeCourtApis();

    openCivilCase();

    expect(courtSession())
        ->type->toBe("Fuqarolik ishlari bo'yicha sud (FK, OK va FPK)")
        ->case_text->toStartWith('📂 ISH № 201')
        ->history->toStartWith('Qarshi tomon: Muhtaram sud!');
    expect(sentTelegramTexts())->sequence(
        fn ($text) => $text->toStartWith('📂 ISH № 201'),
        fn ($text) => $text->toStartWith("👔 Javobgar (katta o'g'il) advokati:\n«Muhtaram sud!"),
        fn ($text) => $text->toStartWith("👩‍⚖️ Raislik qiluvchi (Sudya):\n«Da'vogar advokati"),
    );
});

it('asks the user to pick a court when no hearing is open', function () {
    fakeCourtApis();

    sendToCourtBot(['text' => 'Salom']);

    expect(sentTelegramTexts())->toBe(["⚠️ Iltimos, avval pastdagi menyudan sud yo'nalishini tanlang:"]);
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'generateContent'));
});

it('replies to a written speech with the opponent and the judge and records it in the history', function () {
    fakeCourtApis("[QARSHI_TOMON]\nQarshi tomon: Vasiyat qonuniy.\n[SUDYA]\nSudya: Qaysi modda?");
    openCivilCase();

    sendToCourtBot(['text' => 'FK 1142-moddasi majburiy ulushni kafolatlaydi.']);

    expect(array_slice(sentTelegramTexts(), 3))->toBe([
        "👔 Javobgar (katta o'g'il) advokati:\n«Vasiyat qonuniy.»",
        "👩‍⚖️ Raislik qiluvchi (Sudya):\n«Qaysi modda?»",
    ]);
    expect(courtSession()['history'])->toEndWith(
        "Advokat: FK 1142-moddasi majburiy ulushni kafolatlaydi.\nQarshi tomon: Vasiyat qonuniy.\nSudya: Qaysi modda?\n---\n",
    );
});

it('keeps the hearing open when the speech merely mentions a verdict', function () {
    fakeCourtApis("[QARSHI_TOMON]\nE'tiroz.\n[SUDYA]\nDavom eting.");
    openCivilCase();

    sendToCourtBot(['text' => 'Birinchi instansiya hukmi noqonuniy, ishni yakunlash mumkin emas.']);

    expect(courtSession())->not->toBeNull();
    expect(sentTelegramTexts())->toContain("👩‍⚖️ Raislik qiluvchi (Sudya):\n«Davom eting.»");
});

it('announces the verdict and closes the hearing when the verdict button is pressed', function () {
    fakeCourtApis('Da\'vo qanoatlantirildi. Baho: 9/10.');
    openCivilCase();

    sendToCourtBot(['text' => CourtBot::BUTTON_VERDICT]);

    expect(courtSession())->toBeNull();
    expect(last(sentTelegramTexts()))->toStartWith("🏛 SUD MASLAHATXONADAN QAYTDI.\n\n📜 YAKUNIY SUD HUJJATI:\n\nDa'vo qanoatlantirildi. Baho: 9/10.");
});

it('transcribes a voice speech through Gemini and shows the transcript', function () {
    fakeCourtApis("[STENOGRAMMA]\nMen FK 1142-moddasiga tayanaman.\n[QARSHI_TOMON]\nE'tiroz.\n[SUDYA]\nDalil bormi?");
    openCivilCase();

    sendToCourtBot(['voice' => ['file_id' => 'voice-file', 'mime_type' => 'audio/ogg']]);

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'generateContent')
        && $request['contents'][0]['parts'][0]['inline_data'] === ['mime_type' => 'audio/ogg', 'data' => base64_encode('OGG-BYTES')]);
    expect(sentTelegramTexts())->toContain("📝 Sud majlisi kotibi (Ovozli nutq bayonnomasi):\n«Men FK 1142-moddasiga tayanaman.»");
    expect(courtSession()['history'])->toContain('Advokat: Men FK 1142-moddasiga tayanaman.');
});

it('tells the user to retry and keeps the history when Gemini fails', function () {
    fakeCourtApis();
    openCivilCase();
    $historyBefore = courtSession()['history'];

    sendToCourtBot(['text' => 'Mening pozitsiyam.']);

    expect(last(sentTelegramTexts()))->toBe("⚠️ Aloqada kichik uzilish bo'ldi. Iltimos, fikringizni yana bir marta yuboring.");
    expect(courtSession()['history'])->toBe($historyBefore);
});

it('drops a late reply when the user closed the hearing while Gemini was answering', function () {
    fakeCourtApis(function () {
        app(CourtSessionStore::class)->forget(COURT_CHAT_ID);

        return Http::response(['candidates' => [['content' => ['parts' => [['text' => "[SUDYA]\nKech qolgan javob."]]]]]]);
    });
    openCivilCase();

    sendToCourtBot(['text' => 'Mening pozitsiyam.']);

    expect(sentTelegramTexts())->toHaveCount(3);
});

it('sends a hint from the mentor for the open case', function () {
    fakeCourtApis('FK 1142-moddasiga tayaning.');
    openCivilCase();

    sendToCourtBot(['text' => CourtBot::BUTTON_HINT]);

    expect(last(sentTelegramTexts()))->toBe("💡 Ustoz advokat maslahati:\n\nFK 1142-moddasiga tayaning.");
});

it('replaces the case with a generated one and restarts the history', function () {
    fakeCourtApis("[ISH_HOLATI]\nISH № 900 | Ijara nizosi.\n[QARSHI_TOMON]\nIjara haqi to'lanmagan.\n[SUDYA]\nShartnoma bormi?");
    openCivilCase();

    sendToCourtBot(['text' => CourtBot::BUTTON_NEW_CASE]);

    expect(courtSession())
        ->case_text->toBe('ISH № 900 | Ijara nizosi.')
        ->history->toBe("Qarshi tomon: Ijara haqi to'lanmagan.\nSudya: Shartnoma bormi?\n---\n");
    expect(array_slice(sentTelegramTexts(), 3))->toBe([
        "🆕 YANGI SUD ISHI:\n\nISH № 900 | Ijara nizosi.",
        "👔 Javobgar (katta o'g'il) advokati:\n«Ijara haqi to'lanmagan.»",
        "👩‍⚖️ Raislik qiluvchi (Sudya):\n«Shartnoma bormi?»",
    ]);
});

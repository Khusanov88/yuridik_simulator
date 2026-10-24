<?php

namespace App\Services\Court;

use App\Services\GeminiClient;
use App\Services\TelegramClient;
use Illuminate\Support\Str;

/**
 * Runs the Telegram court simulator: one incoming message in, the court's replies out.
 *
 * @phpstan-import-type CourtSession from CourtSessionStore
 */
class CourtBot
{
    public const string BUTTON_MAIN_MENU = '🔙 Asosiy menyu';

    public const string BUTTON_VERDICT = '🏁 Sudni yakunlash (Hukm)';

    public const string BUTTON_HINT = '💡 Huquqiy maslahat';

    public const string BUTTON_NEW_CASE = '✨ Yangi kazus tuzish (AI)';

    private const int HISTORY_LENGTH = 3000;

    private const int HINT_HISTORY_LENGTH = 1500;

    private const string JUDGE_TITLE = '👩‍⚖️ Raislik qiluvchi (Sudya)';

    private const string CONNECTION_ERROR = "⚠️ Aloqada kichik uzilish bo'ldi. Iltimos, fikringizni yana bir marta yuboring.";

    public function __construct(
        private TelegramClient $telegram,
        private GeminiClient $gemini,
        private CourtSessionStore $sessions,
        private CourtTagParser $parser,
    ) {}

    /**
     * @param  array<string, mixed>  $message  The "message" object of a Telegram update.
     */
    public function handle(array $message): void
    {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');
        $voice = $message['voice'] ?? $message['audio'] ?? null;

        if ($text === '/start' || $text === self::BUTTON_MAIN_MENU) {
            $this->welcome($chatId);

            return;
        }

        $cases = config('court.cases');

        if (isset($cases[$text])) {
            $this->openCase($chatId, $cases[$text]);

            return;
        }

        $session = $this->sessions->get($chatId);

        if ($session === null) {
            $this->telegram->sendMessage($chatId, "⚠️ Iltimos, avval pastdagi menyudan sud yo'nalishini tanlang:", $this->mainMenuKeyboard());

            return;
        }

        match ($text) {
            self::BUTTON_HINT => $this->sendHint($chatId, $session),
            self::BUTTON_NEW_CASE => $this->generateCase($chatId, $session),
            self::BUTTON_VERDICT => $this->announceVerdict($chatId, $session),
            default => $this->continueHearing($chatId, $session, $text, is_array($voice) ? $voice : null),
        };
    }

    private function welcome(int|string $chatId): void
    {
        $this->sessions->forget($chatId);

        $this->telegram->sendMessage(
            $chatId,
            "⚖️ Assalomu alaykum! «Yuridik Simulyator» interaktiv sud platformasiga xush kelibsiz!\n\n"
                ."🎙 Endi sudda YOZMA yoki OVOZLI XABAR (mikrofon) orqali jonli nutq so'zlashingiz mumkin!\n\n"
                ."👇 Sud majlisini boshlash uchun yo'nalishni tanlang:",
            $this->mainMenuKeyboard(),
        );
    }

    /**
     * @param  array{type: string, opponent_title: string, title: string, desc: string, opponent_first: string, judge_first: string}  $case
     */
    private function openCase(int|string $chatId, array $case): void
    {
        $this->sessions->put($chatId, [
            'session_id' => $this->newSessionId(),
            'type' => $case['type'],
            'opponent_title' => $case['opponent_title'],
            'case_text' => $case['title']."\n".$case['desc'],
            'history' => $this->openingHistory($case['opponent_first'], $case['judge_first']),
        ]);

        $this->telegram->sendMessage($chatId, $case['title']."\n\n".$case['desc'], $this->courtKeyboard());
        $this->telegram->sendMessage($chatId, $case['opponent_title'].":\n«".$case['opponent_first'].'»');
        $this->telegram->sendMessage($chatId, self::JUDGE_TITLE.":\n«".$case['judge_first'].'»', $this->courtKeyboard());
    }

    /**
     * @param  CourtSession  $session
     */
    private function sendHint(int|string $chatId, array $session): void
    {
        $this->telegram->sendChatAction($chatId);

        $hint = $this->gemini->generate(
            "Siz tajribali ustoz advokatsiz. Hozir '".$session['type']."'da quyidagi ish ko'rilmoqda:\n"
                .$session['case_text']."\n\n"
                ."Oxirgi suhbat:\n".Str::substr($session['history'], -self::HINT_HISTORY_LENGTH)."\n\n"
                ."TOPSHIRIQ: Advokatga sudyaga qanday javob berish kerakligi haqida 3-4 qatorda aniq HUQUQIY MASLAHAT bering (qaysi Kodeks va nechanchi moddalarni aytishi kerakligini ko'rsating). Yulduzchalar ishlatmang.",
        );

        if ($hint !== null && $this->sessions->isActive($chatId, $session['session_id'])) {
            $this->telegram->sendMessage($chatId, "💡 Ustoz advokat maslahati:\n\n".$hint, $this->courtKeyboard());
        }
    }

    /**
     * @param  CourtSession  $session
     */
    private function generateCase(int|string $chatId, array $session): void
    {
        $session['session_id'] = $this->newSessionId();
        $this->sessions->put($chatId, $session);

        $this->telegram->sendChatAction($chatId);

        $reply = $this->gemini->generate(
            "O'zbekiston Respublikasi qonunchiligi asosida '".$session['type']."' yo'nalishi bo'yicha yangi, qiziqarli kazus tuzing.\n"
                ."Javobingizni qat'iy ravishda quyidagi 3 ta tegga bo'lib yozing (teg ichida rol nomini qayta yozmang, yulduzcha ishlatmang):\n"
                ."[ISH_HOLATI]\nIsh raqami, qisqa fabula hamda foydalanuvchi qaysi taraf advokati ekani.\n"
                ."[QARSHI_TOMON]\nQarshi tarafning 2 gapdan iborat keskin da'vosi.\n"
                ."[SUDYA]\nSudyaning advokatga beradigan 1-2 gaplik dastlabki savoli.",
        );

        if (! $this->sessions->isActive($chatId, $session['session_id'])) {
            return;
        }

        if ($reply === null) {
            $this->telegram->sendMessage($chatId, self::CONNECTION_ERROR, $this->courtKeyboard());

            return;
        }

        $sections = $this->parser->parse($reply, ['ISH_HOLATI', 'QARSHI_TOMON', 'SUDYA']);
        $caseText = $sections['ish_holati'] ?: $reply;
        $opponentText = $sections['qarshi_tomon'] ?: "Da'vo talablarimiz to'liq qanoatlantirilishini so'raymiz!";
        $judgeText = $sections['sudya'] ?: 'Advokat, ish holati bo\'yicha huquqiy pozitsiyangizni bayon qiling.';

        $session['case_text'] = $caseText;
        $session['history'] = $this->openingHistory($opponentText, $judgeText);
        $this->sessions->put($chatId, $session);

        $this->telegram->sendMessage($chatId, "🆕 YANGI SUD ISHI:\n\n".$caseText, $this->courtKeyboard());
        $this->telegram->sendMessage($chatId, $session['opponent_title'].":\n«".$opponentText.'»');
        $this->telegram->sendMessage($chatId, self::JUDGE_TITLE.":\n«".$judgeText.'»', $this->courtKeyboard());
    }

    /**
     * Close the hearing first, so replies still being generated for it are dropped.
     *
     * @param  CourtSession  $session
     */
    private function announceVerdict(int|string $chatId, array $session): void
    {
        $this->sessions->forget($chatId);

        $this->telegram->sendChatAction($chatId);

        $verdict = $this->gemini->generate(
            "Siz O'zbekiston Respublikasining ".$session['type'].' raisi (Sudya)siz. Sana: '.now()->format('d.m.Y')."-yil.\n\n"
                ."KO'RILAYOTGAN ISH:\n".$session['case_text']."\n\n"
                ."SUD MUZOKARASI TARIXI:\n".Str::substr($session['history'], -self::HISTORY_LENGTH)."\n\n"
                ."QAT'IY TOPSHIRIQ:\n"
                ."Sud muhokamasi rasman YAKUNLANDI! Boshqa savol bermang!\n"
                ."O'zbekiston Respublikasining tegishli Kodeksi va Qonun moddalariga asoslanib YAKUNIY HUKM / HAL QILUV QARORINI e'lon qiling:\n"
                ."1. Huquqiy asoslar;\n"
                ."2. SUD QARORI: Da'vo/shikoyat qanoatlantirildimi, qismanmi yoki rad etildimi;\n"
                ."3. ADVOKATGA BAHO: Foydalanuvchining ishtirokiga 10 ballik tizimda baho va qisqa xulosa bering.\n"
                .'(Matnda ** yulduzchalar ishlatmang).',
        );

        $this->telegram->sendMessage(
            $chatId,
            $verdict !== null
                ? "🏛 SUD MASLAHATXONADAN QAYTDI.\n\n📜 YAKUNIY SUD HUJJATI:\n\n".$verdict."\n\n🔄 Yangi sud majlisini boshlash uchun pastdagi menyudan yo'nalishni tanlang:"
                : "⚠️ Hukm chiqarishda kichik uzilish bo'ldi. Yangi sud boshlash uchun menyudan tanlang:",
            $this->mainMenuKeyboard(),
        );
    }

    /**
     * @param  CourtSession  $session
     * @param  array<string, mixed>|null  $voice
     */
    private function continueHearing(int|string $chatId, array $session, string $text, ?array $voice): void
    {
        $sessionId = $session['session_id'];
        $audio = null;

        if (isset($voice['file_id'])) {
            $this->telegram->sendChatAction($chatId);
            $audio = $this->telegram->downloadAudio($voice['file_id'], $voice['mime_type'] ?? 'audio/ogg');

            if ($audio === null) {
                if ($this->sessions->isActive($chatId, $sessionId)) {
                    $this->telegram->sendMessage($chatId, "⚠️ Ovozli xabarni o'qishda uzilish bo'ldi. Iltimos, qaytadan yuboring.", $this->courtKeyboard());
                }

                return;
            }
        }

        if ($text === '' && $audio === null) {
            return;
        }

        $this->telegram->sendChatAction($chatId);

        $history = Str::substr($session['history'], -self::HISTORY_LENGTH);
        $reply = $this->gemini->generate($this->hearingPrompt($session, $history, $text, $audio !== null), $audio);

        if (! $this->sessions->isActive($chatId, $sessionId)) {
            return;
        }

        if ($reply === null) {
            $this->telegram->sendMessage($chatId, self::CONNECTION_ERROR, $this->courtKeyboard());

            return;
        }

        $sections = $this->parser->parse($reply, ['STENOGRAMMA', 'QARSHI_TOMON', 'SUDYA']);
        $advocateSpeech = $text;

        if ($audio !== null && $sections['stenogramma'] !== '') {
            $advocateSpeech = $sections['stenogramma'];
            $this->telegram->sendMessage($chatId, "📝 Sud majlisi kotibi (Ovozli nutq bayonnomasi):\n«".$advocateSpeech.'»');
        }

        if ($sections['qarshi_tomon'] !== '' && $this->sessions->isActive($chatId, $sessionId)) {
            $this->telegram->sendMessage($chatId, $session['opponent_title'].":\n«".$sections['qarshi_tomon'].'»');
        }

        if (! $this->sessions->isActive($chatId, $sessionId)) {
            return;
        }

        $this->telegram->sendMessage(
            $chatId,
            $sections['sudya'] !== '' ? self::JUDGE_TITLE.":\n«".$sections['sudya'].'»' : "👩‍⚖️ Sud majlisi:\n".$reply,
            $this->courtKeyboard(),
        );

        $latestSession = $this->sessions->get($chatId);

        if ($latestSession !== null && $latestSession['session_id'] === $sessionId) {
            $latestSession['history'] = $history
                .'Advokat: '.$advocateSpeech."\n"
                .'Qarshi tomon: '.$sections['qarshi_tomon']."\n"
                .'Sudya: '.($sections['sudya'] ?: $reply)."\n---\n";
            $this->sessions->put($chatId, $latestSession);
        }
    }

    /**
     * @param  CourtSession  $session
     */
    private function hearingPrompt(array $session, string $history, string $text, bool $hasAudio): string
    {
        $prompt = "Siz O'zbekiston Respublikasining ".$session['type']." majlisini olib boryapsiz.\n"
            ."KO'RILAYOTGAN ISH:\n".$session['case_text']."\n\n"
            ."OLDINGI MUZOKARA TARIXI:\n".$history."\n\n";

        if ($hasAudio) {
            $prompt .= "Foydalanuvchi (Advokat) o'z nutqini OVOZLI XABAR shaklida yubordi.\n"
                ."QAT'IY TOPSHIRIQ — Javobingizni faqat quyidagi 3 ta tegga bo'lib yozing (matn boshida rol nomini qayta yozmang, to'g'ridan-to'g'ri gapni yozing):\n\n"
                ."[STENOGRAMMA]\nAdvokat ovozli xabarda nima deganini o'zbek tilida aniq yozing.\n\n";
        } else {
            $prompt .= "ADVOKATNING YANGI NUTQI:\n".$text."\n\n"
                ."QAT'IY TOPSHIRIQ — Javobingizni faqat quyidagi 2 ta tegga bo'lib yozing (matn boshida rol nomini qayta yozmang, to'g'ridan-to'g'ri gapni yozing):\n\n";
        }

        return $prompt
            ."[QARSHI_TOMON]\nQarshi taraf nomidan advokatning fikriga qarshi 2 gapdan iborat QISQA va JONLI e'tiroz yozing.\n\n"
            ."[SUDYA]\nSudya nomidan qisqa (2 gap) munosabat bildiring va ish bo'yicha BITTADAN ortiq bo'lmagan aniq savol bering. Yulduzchalar (**) ishlatmang!";
    }

    private function openingHistory(string $opponentText, string $judgeText): string
    {
        return 'Qarshi tomon: '.$opponentText."\nSudya: ".$judgeText."\n---\n";
    }

    private function newSessionId(): string
    {
        return (string) Str::uuid();
    }

    /**
     * Two case buttons per row, in the order the cases are configured.
     *
     * @return array{keyboard: list<list<array{text: string}>>, resize_keyboard: bool}
     */
    private function mainMenuKeyboard(): array
    {
        return [
            'keyboard' => collect(array_keys(config('court.cases')))
                ->map(fn (string $label): array => ['text' => $label])
                ->chunk(2)
                ->map(fn ($row): array => $row->values()->all())
                ->values()
                ->all(),
            'resize_keyboard' => true,
        ];
    }

    /**
     * @return array{keyboard: list<list<array{text: string}>>, resize_keyboard: bool}
     */
    private function courtKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => self::BUTTON_VERDICT], ['text' => self::BUTTON_HINT]],
                [['text' => self::BUTTON_NEW_CASE], ['text' => self::BUTTON_MAIN_MENU]],
            ],
            'resize_keyboard' => true,
        ];
    }
}

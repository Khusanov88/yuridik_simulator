<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.gemini.key' => 'gemini-key']);
});

function fakeGeminiReply(?string $reply): void
{
    Http::preventStrayRequests();

    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response([
            'models' => [['name' => 'models/gemini-test-flash', 'supportedGenerationMethods' => ['generateContent']]],
        ]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-test-flash:generateContent' => $reply === null
            ? Http::failedConnection()
            : Http::response(['candidates' => [['content' => ['parts' => [['text' => $reply]]]]]]),
    ]);
}

it('returns the opponent and the judge for a written court speech', function () {
    fakeGeminiReply("[STENOGRAMMA]\nNutq.\n[QARSHI_TOMON]\nE'tiroz bildiramiz.\n[SUDYA]\nQaysi dalil?");

    $response = $this->postJson(route('api.court.turn'), [
        'court_type' => 'Fuqarolik sudi',
        'user_text' => 'FK 1142-moddasi.',
    ]);

    $response->assertOk()->assertExactJson([
        'ok' => true,
        'transcript' => 'Nutq.',
        'opponent' => "E'tiroz bildiramiz.",
        'judge' => 'Qaysi dalil?',
    ]);
});

it('sends a recorded court speech to Gemini as audio', function () {
    fakeGeminiReply("[STENOGRAMMA]\nOvozli nutq.\n[SUDYA]\nSavol?");

    $response = $this->postJson(route('api.court.turn'), [
        'audio_base64' => base64_encode('WEBM'),
        'audio_mime' => 'audio/webm;codecs=opus',
    ]);

    $response->assertOk()->assertJsonPath('transcript', 'Ovozli nutq.');
    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'generateContent')
        && $request['contents'][0]['parts'][0]['inline_data'] === ['mime_type' => 'audio/webm;codecs=opus', 'data' => base64_encode('WEBM')]);
});

it('returns 422 when a court speech has neither text nor audio', function () {
    Http::preventStrayRequests();

    $response = $this->postJson(route('api.court.turn'), ['court_type' => 'Fuqarolik sudi']);

    $response->assertUnprocessable()->assertInvalid(['user_text' => 'required when audio base64 is not present']);
});

it('returns 422 when the audio type is not audio', function () {
    Http::preventStrayRequests();

    $response = $this->postJson(route('api.court.turn'), [
        'audio_base64' => base64_encode('x'),
        'audio_mime' => 'text/html',
    ]);

    $response->assertUnprocessable()->assertInvalid(['audio_mime' => 'must start with one of the following: audio/']);
});

it('reports a failed court turn when Gemini is unreachable', function () {
    fakeGeminiReply(null);

    $response = $this->postJson(route('api.court.turn'), ['user_text' => 'Nutq.']);

    $response->assertOk()->assertExactJson(['ok' => false, 'error' => "Aloqada uzilish bo'ldi."]);
});

it('answers an assistant question', function () {
    fakeGeminiReply('1. Mehnat kodeksining 157-moddasi...');

    $response = $this->postJson(route('api.assistant.chat'), ['question' => "Ta'til necha kun?", 'mode' => 'general']);

    $response->assertOk()->assertExactJson(['ok' => true, 'answer' => '1. Mehnat kodeksining 157-moddasi...']);
});

it('returns 422 for an unknown assistant mode', function () {
    Http::preventStrayRequests();

    $response = $this->postJson(route('api.assistant.chat'), ['question' => 'Savol', 'mode' => 'hack']);

    $response->assertUnprocessable()->assertInvalid(['mode' => 'is invalid']);
});

it('returns a generated case with its evidence', function () {
    fakeGeminiReply("[ISH_NOMI]\nISH № 1\n[ISH_HOLATI]\nFabula.\n[DALIL_1]\nShartnoma | Mazmun\n[DALIL_2]\nDalolatnoma | Mazmun\n[DALIL_3]\nFK 333 | Mazmun\n[QARSHI_TOMON]\nE'tiroz.\n[SUDYA]\nSavol?");

    $response = $this->postJson(route('api.court.cases.store'), ['court_type' => 'Iqtisodiy sud', 'level_name' => 'Talaba']);

    $response->assertOk()->assertExactJson([
        'ok' => true,
        'title' => 'ISH № 1',
        'desc' => 'Fabula.',
        'evidences' => ['Shartnoma | Mazmun', 'Dalolatnoma | Mazmun', 'FK 333 | Mazmun'],
        'opponent' => "E'tiroz.",
        'judge' => 'Savol?',
    ]);
});

it('returns the verdict for the hearing', function () {
    fakeGeminiReply('Hal qiluv qarori. Baho: 85/100.');

    $response = $this->postJson(route('api.court.verdict'), ['court_type' => 'Fuqarolik sudi', 'history' => 'Advokat: ...']);

    $response->assertOk()->assertExactJson(['ok' => true, 'result' => 'Hal qiluv qarori. Baho: 85/100.']);
});

it('extracts the news list from a reply wrapped in a code fence', function () {
    fakeGeminiReply("```json\n[{\"title\":\"Yangi Mehnat qoidasi\",\"category\":\"Mehnat\",\"date\":\"2026\",\"summary\":\"Qisqa.\"}]\n```");

    $response = $this->postJson(route('api.news.refresh'));

    $response->assertOk()->assertExactJson([
        'ok' => true,
        'news' => [['title' => 'Yangi Mehnat qoidasi', 'category' => 'Mehnat', 'date' => '2026', 'summary' => 'Qisqa.']],
    ]);
});

it('returns 429 after 15 AI requests in a minute from one address', function () {
    fakeGeminiReply('[]');

    foreach (range(1, 15) as $attempt) {
        $this->postJson(route('api.news.refresh'))->assertOk();
    }

    $this->postJson(route('api.news.refresh'))->assertTooManyRequests();
});

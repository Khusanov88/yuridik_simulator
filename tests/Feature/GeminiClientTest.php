<?php

use App\Services\GeminiClient;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.gemini.key' => 'gemini-key', 'services.gemini.max_model_attempts' => 3]);
});

function geminiTextResponse(string $text): PromiseInterface
{
    return Http::response(['candidates' => [['content' => ['parts' => [['text' => $text]]]]]]);
}

it('tries lite models first and falls back to the next model on failure', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response(['models' => [
            ['name' => 'models/gemini-2.5-flash', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-2.5-flash-preview-tts', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-2.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-2.5-pro', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/text-embedding-004', 'supportedGenerationMethods' => ['embedContent']],
        ]]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent' => Http::failedConnection(),
        'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => geminiTextResponse('**Javob**'),
    ]);

    $reply = app(GeminiClient::class)->generate('Savol');

    expect($reply)->toBe('Javob');
    expect(collect(Http::recorded())->map(fn (array $pair): string => $pair[0]->url())->all())->toBe([
        'https://generativelanguage.googleapis.com/v1beta/models',
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent',
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent',
    ]);
});

it('falls back to the default models when the model list is unavailable', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::failedConnection(),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent' => geminiTextResponse('Javob'),
    ]);

    expect(app(GeminiClient::class)->generate('Savol'))->toBe('Javob');
});

it('returns null when no model produces text', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response(['models' => [
            ['name' => 'models/gemini-2.5-flash', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent' => Http::response(['error' => ['code' => 429]], 429),
    ]);

    expect(app(GeminiClient::class)->generate('Savol'))->toBeNull();
});

it('sends the API key in a header instead of the URL', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::failedConnection(),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent' => geminiTextResponse('Javob'),
    ]);

    app(GeminiClient::class)->generate('Savol');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), 'generateContent')
        && $request->hasHeader('x-goog-api-key', 'gemini-key')
        && ! str_contains($request->url(), 'gemini-key'));
});

it('replaces invalid UTF-8 in the prompt instead of failing', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::failedConnection(),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent' => geminiTextResponse('Javob'),
    ]);

    expect(app(GeminiClient::class)->generate("Hujjat \xB1 matni"))->toBe('Javob');
});

it('skips a retired model without using up an attempt and remembers it', function () {
    config(['services.gemini.max_model_attempts' => 1]);
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response(['models' => [
            ['name' => 'models/gemini-2.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-3.5-flash-lite', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent' => Http::response(['error' => ['message' => 'This model is no longer available to new users.']], 404),
        'generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent' => geminiTextResponse('Javob'),
    ]);
    $gemini = app(GeminiClient::class);

    expect($gemini->generate('Birinchi savol'))->toBe('Javob');
    expect($gemini->generate('Ikkinchi savol'))->toBe('Javob');

    Http::assertSentCount(4);
});

it('stops trying other models when the key is denied access', function () {
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response(['models' => [
            ['name' => 'models/gemini-flash-lite-latest', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-flash-latest', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent' => Http::response(['error' => ['message' => 'Your project has been denied access.']], 403),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => geminiTextResponse('Javob'),
    ]);
    $gemini = app(GeminiClient::class);

    expect($gemini->generate('Savol'))->toBeNull();
    expect($gemini->lastError())->toBe('models/gemini-flash-lite-latest (HTTP 403): Your project has been denied access.');
    Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'gemini-flash-latest:generateContent'));
});

it('tries preview models only after stable ones', function () {
    config(['services.gemini.max_model_attempts' => 1]);
    Http::preventStrayRequests();
    Http::fake([
        'generativelanguage.googleapis.com/v1beta/models' => Http::response(['models' => [
            ['name' => 'models/gemini-3.1-flash-lite-preview', 'supportedGenerationMethods' => ['generateContent']],
            ['name' => 'models/gemini-flash-latest', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        'generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent' => geminiTextResponse('Javob'),
    ]);

    expect(app(GeminiClient::class)->generate('Savol'))->toBe('Javob');
});

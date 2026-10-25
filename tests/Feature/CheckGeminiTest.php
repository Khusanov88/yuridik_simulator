<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

const GEMINI_MODELS_URL = 'generativelanguage.googleapis.com/v1beta/models';

it('fails with a hint when no API key is configured', function () {
    config(['services.gemini.key' => null]);
    Http::preventStrayRequests();

    $this->artisan('gemini:check')
        ->expectsOutputToContain("GEMINI_API_KEY bo'sh")
        ->assertFailed();
});

it('shows the reason Google gives for rejecting the key', function () {
    config(['services.gemini.key' => 'revoked-key']);
    Http::preventStrayRequests();
    Http::fake([
        GEMINI_MODELS_URL => Http::response(['error' => ['code' => 400, 'message' => 'Key invalid']], 400),
    ]);

    $this->artisan('gemini:check')
        ->expectsOutputToContain('(HTTP 400): Key invalid')
        ->assertFailed();
});

it('shows the connection error when Gemini cannot be reached', function () {
    config(['services.gemini.key' => 'gemini-key']);
    Http::preventStrayRequests();
    Http::fake([GEMINI_MODELS_URL => Http::failedConnection()]);

    $this->artisan('gemini:check')
        ->expectsOutputToContain("Gemini serveriga ulanib bo'lmadi")
        ->assertFailed();
});

it('confirms Gemini works when a reply is generated', function () {
    config(['services.gemini.key' => 'AIzaTestKey1234']);
    Http::preventStrayRequests();
    Http::fake([
        GEMINI_MODELS_URL => Http::response(['models' => [
            ['name' => 'models/gemini-test-flash', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        GEMINI_MODELS_URL.'/gemini-test-flash:generateContent' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Salom']]]]]]),
    ]);

    $this->artisan('gemini:check')
        ->expectsOutputToContain('Ishlatilayotgan kalit: AIzaTe*****1234')
        ->expectsOutputToContain('Gemini ishlayapti. Javob: Salom')
        ->assertSuccessful();
});

it('logs why a generation attempt failed', function () {
    config(['services.gemini.key' => 'gemini-key']);
    Log::spy();
    Http::preventStrayRequests();
    Http::fake([
        GEMINI_MODELS_URL => Http::response(['models' => [
            ['name' => 'models/gemini-test-flash', 'supportedGenerationMethods' => ['generateContent']],
        ]]),
        GEMINI_MODELS_URL.'/gemini-test-flash:generateContent' => Http::response(['error' => ['message' => 'Quota exceeded']], 429),
    ]);

    $this->artisan('gemini:check')
        ->expectsOutputToContain('Sababi: models/gemini-test-flash (HTTP 429): Quota exceeded')
        ->assertFailed();

    Log::shouldHaveReceived('warning')->with('Gemini returned no text.', ['model' => 'models/gemini-test-flash', 'status' => 429, 'error' => 'Quota exceeded']);
});

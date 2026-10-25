<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiClient
{
    private const string BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    private const string MODELS_CACHE_KEY = 'gemini:models';

    private const string RETIRED_MODELS_CACHE_KEY = 'gemini:retired-models';

    /**
     * Models tried when the model list cannot be fetched.
     *
     * @var list<string>
     */
    private const array FALLBACK_MODELS = [
        'models/gemini-flash-lite-latest',
        'models/gemini-flash-latest',
    ];

    /**
     * Statuses caused by the key or the request itself, so every other model would fail the same way.
     *
     * @var list<int>
     */
    private const array FATAL_STATUSES = [400, 401, 403];

    private ?string $lastError = null;

    /**
     * Generate a reply, falling back to the next available model on failure.
     *
     * @param  array{mime_type: string, data: string}|null  $audio  Base64-encoded audio sent alongside the prompt.
     */
    public function generate(string $prompt, ?array $audio = null, float $temperature = 0.6, int $maxOutputTokens = 1000): ?string
    {
        $parts = [];

        if ($audio !== null) {
            $parts[] = ['inline_data' => $audio];
        }

        $parts[] = ['text' => mb_scrub($prompt, 'UTF-8')];

        $payload = [
            'contents' => [['parts' => $parts]],
            'generationConfig' => [
                'temperature' => $temperature,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        $this->lastError = null;
        $attemptsLeft = max(1, (int) config('services.gemini.max_model_attempts'));

        foreach ($this->availableModels() as $model) {
            try {
                $response = $this->request()->timeout(25)->post("{$model}:generateContent", $payload);
            } catch (ConnectionException $exception) {
                $this->lastError = "{$model}: ".$exception->getMessage();
                Log::warning('Gemini request could not connect.', ['model' => $model, 'error' => $exception->getMessage()]);

                if (--$attemptsLeft === 0) {
                    break;
                }

                continue;
            }

            $reply = $response->json('candidates.0.content.parts.0.text');

            if (is_string($reply) && trim($reply) !== '') {
                return trim(str_replace('*', '', $reply));
            }

            $this->lastError = "{$model} (HTTP {$response->status()}): ".$this->errorMessage($response);
            Log::warning('Gemini returned no text.', ['model' => $model, 'status' => $response->status(), 'error' => $this->errorMessage($response)]);

            // A retired model fails instantly, so it does not use up an attempt and is skipped from now on.
            if ($response->notFound()) {
                $this->retireModel($model);

                continue;
            }

            if (in_array($response->status(), self::FATAL_STATUSES, true) || --$attemptsLeft === 0) {
                break;
            }
        }

        return null;
    }

    /**
     * Why the last generate() call returned null.
     */
    public function lastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Explain why Gemini cannot be used right now, or null when it is reachable with the configured key.
     */
    public function diagnose(): ?string
    {
        if ((string) config('services.gemini.key') === '') {
            return "GEMINI_API_KEY bo'sh. Uni .env faylga yozing va `php artisan config:clear` ni ishga tushiring.";
        }

        try {
            $response = $this->request()->timeout(15)->get('models');
        } catch (ConnectionException $exception) {
            return "Gemini serveriga ulanib bo'lmadi: ".$exception->getMessage();
        }

        if ($response->failed()) {
            return "Gemini so'rovni rad etdi (HTTP {$response->status()}): ".$this->errorMessage($response);
        }

        return null;
    }

    private function errorMessage(Response $response): string
    {
        return (string) ($response->json('error.message') ?? $response->json('promptFeedback.blockReason') ?? Str::limit($response->body(), 300));
    }

    /**
     * Text-capable Gemini models, fastest ("lite", then "flash") first and previews last.
     *
     * @return list<string>
     */
    private function availableModels(): array
    {
        $retiredModels = Cache::get(self::RETIRED_MODELS_CACHE_KEY, []);

        return array_values(array_diff($this->listedModels(), $retiredModels));
    }

    private function retireModel(string $model): void
    {
        $retiredModels = Cache::get(self::RETIRED_MODELS_CACHE_KEY, []);

        Cache::put(self::RETIRED_MODELS_CACHE_KEY, array_values(array_unique([...$retiredModels, $model])), now()->addDay());
    }

    /**
     * @return list<string>
     */
    private function listedModels(): array
    {
        $cachedModels = Cache::get(self::MODELS_CACHE_KEY);

        if (is_array($cachedModels) && $cachedModels !== []) {
            return $cachedModels;
        }

        try {
            $response = $this->request()->timeout(8)->get('models');
        } catch (ConnectionException $exception) {
            Log::warning('Gemini model list could not connect.', ['error' => $exception->getMessage()]);

            return self::FALLBACK_MODELS;
        }

        if ($response->failed()) {
            Log::warning('Gemini model list request failed.', ['status' => $response->status(), 'error' => $this->errorMessage($response)]);
        }

        $models = collect($response->json('models') ?? [])
            ->filter(fn (array $model): bool => in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true)
                && Str::contains($model['name'] ?? '', 'gemini')
                && Str::contains($model['name'], ['lite', 'flash'])
                && ! Str::contains($model['name'], ['tts', 'image', 'embedding']))
            ->sortBy(fn (array $model): int => (Str::contains($model['name'], 'preview') ? 2 : 0) + (Str::contains($model['name'], 'lite') ? 0 : 1))
            ->pluck('name')
            ->values()
            ->all();

        if ($models === []) {
            return self::FALLBACK_MODELS;
        }

        Cache::put(self::MODELS_CACHE_KEY, $models, now()->addHour());

        return $models;
    }

    private function request(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withHeaders(['x-goog-api-key' => (string) config('services.gemini.key')])
            ->acceptJson()
            ->connectTimeout(6);
    }
}

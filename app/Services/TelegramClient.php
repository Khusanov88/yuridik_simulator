<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TelegramClient
{
    private const string BASE_URL = 'https://api.telegram.org';

    /**
     * Telegram rejects messages longer than 4096 characters.
     */
    private const int MESSAGE_CHUNK_LENGTH = 4000;

    /**
     * Send a message, splitting it into chunks when it is too long.
     *
     * The reply keyboard is attached to the last chunk only.
     *
     * @param  array<string, mixed>|null  $replyMarkup
     */
    public function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): void
    {
        $chunks = mb_str_split($text, self::MESSAGE_CHUNK_LENGTH);

        foreach ($chunks as $index => $chunk) {
            $payload = ['chat_id' => $chatId, 'text' => $chunk];

            if ($replyMarkup !== null && $index === array_key_last($chunks)) {
                $payload['reply_markup'] = $replyMarkup;
            }

            $this->call('sendMessage', $payload);
        }
    }

    public function sendChatAction(int|string $chatId, string $action = 'typing'): void
    {
        $this->call('sendChatAction', ['chat_id' => $chatId, 'action' => $action], timeout: 3);
    }

    /**
     * Download a voice or audio file as base64, ready to be sent to Gemini.
     *
     * @return array{mime_type: string, data: string}|null
     */
    public function downloadAudio(string $fileId, string $mimeType): ?array
    {
        $filePath = $this->call('getFile', ['file_id' => $fileId])?->json('result.file_path');

        if (! is_string($filePath) || $filePath === '') {
            return null;
        }

        try {
            $response = Http::connectTimeout(5)
                ->timeout(15)
                ->get(self::BASE_URL.'/file/bot'.config('services.telegram.token').'/'.$filePath);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        return [
            'mime_type' => $mimeType,
            'data' => base64_encode($response->body()),
        ];
    }

    public function setWebhook(string $url, string $secretToken): bool
    {
        return (bool) $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secretToken,
            'allowed_updates' => ['message'],
        ])?->json('ok');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function call(string $method, array $payload, int $timeout = 10): ?Response
    {
        try {
            return Http::baseUrl(self::BASE_URL.'/bot'.config('services.telegram.token'))
                ->connectTimeout(5)
                ->timeout($timeout)
                ->post($method, $payload);
        } catch (ConnectionException $exception) {
            report($exception);

            return null;
        }
    }
}

<?php

use App\Jobs\ProcessTelegramUpdate;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    config(['services.telegram.webhook_secret' => 'webhook-secret']);
});

function telegramUpdate(int $updateId = 1001): array
{
    return [
        'update_id' => $updateId,
        'message' => [
            'message_id' => 7,
            'date' => 1761300000,
            'chat' => ['id' => 555, 'type' => 'private'],
            'text' => '/start',
        ],
    ];
}

it('returns 403 when the secret token header is missing', function () {
    Queue::fake([ProcessTelegramUpdate::class]);

    $response = $this->postJson(route('telegram.webhook'), telegramUpdate());

    $response->assertForbidden();
    Queue::assertNotPushed(ProcessTelegramUpdate::class);
});

it('returns 403 when the secret token header is wrong', function () {
    Queue::fake([ProcessTelegramUpdate::class]);

    $response = $this->postJson(route('telegram.webhook'), telegramUpdate(), [
        'X-Telegram-Bot-Api-Secret-Token' => 'guessed-secret',
    ]);

    $response->assertForbidden();
    Queue::assertNotPushed(ProcessTelegramUpdate::class);
});

it('returns 403 for every request when no secret is configured', function () {
    config(['services.telegram.webhook_secret' => null]);
    Queue::fake([ProcessTelegramUpdate::class]);

    $response = $this->postJson(route('telegram.webhook'), telegramUpdate(), [
        'X-Telegram-Bot-Api-Secret-Token' => '',
    ]);

    $response->assertForbidden();
    Queue::assertNotPushed(ProcessTelegramUpdate::class);
});

it('dispatches the message once when Telegram retries the same update', function () {
    Queue::fake([ProcessTelegramUpdate::class]);
    $headers = ['X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret'];

    $this->postJson(route('telegram.webhook'), telegramUpdate(), $headers)->assertNoContent();
    $this->postJson(route('telegram.webhook'), telegramUpdate(), $headers)->assertNoContent();

    Queue::assertPushed(ProcessTelegramUpdate::class, 1);
    Queue::assertPushed(ProcessTelegramUpdate::class, fn (ProcessTelegramUpdate $job): bool => $job->message['chat']['id'] === 555
        && $job->message['text'] === '/start');
});

it('does not dispatch anything for updates without a message', function () {
    Queue::fake([ProcessTelegramUpdate::class]);

    $response = $this->postJson(route('telegram.webhook'), ['update_id' => 1002, 'edited_message' => ['text' => 'x']], [
        'X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret',
    ]);

    $response->assertNoContent();
    Queue::assertNotPushed(ProcessTelegramUpdate::class);
});

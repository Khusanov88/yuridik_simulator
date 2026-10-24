<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTelegramUpdate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class TelegramWebhookController extends Controller
{
    /**
     * Acknowledge the update immediately and process it after the response.
     *
     * Telegram re-sends an update with the same "update_id" when it does not get
     * a quick reply, so each update id is only processed once.
     */
    public function __invoke(Request $request): Response
    {
        $secret = (string) config('services.telegram.webhook_secret');

        abort_unless(
            $secret !== '' && hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')),
            403,
        );

        $message = $request->input('message');
        $updateId = $request->integer('update_id');

        if (is_array($message) && isset($message['chat']['id']) && Cache::add("telegram:update:{$updateId}", true, now()->addDay())) {
            ProcessTelegramUpdate::dispatch($message)->onConnection(config('services.telegram.queue_connection'));
        }

        return response()->noContent();
    }
}

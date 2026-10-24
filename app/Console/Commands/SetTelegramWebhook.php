<?php

namespace App\Console\Commands;

use App\Services\TelegramClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('telegram:set-webhook {url? : Public HTTPS URL of the webhook (defaults to the telegram.webhook route)}')]
#[Description('Register the bot webhook with Telegram, protected by the configured secret token')]
class SetTelegramWebhook extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(TelegramClient $telegram): int
    {
        $secret = (string) config('services.telegram.webhook_secret');

        if ($secret === '' || (string) config('services.telegram.token') === '') {
            $this->components->error('Set TELEGRAM_BOT_TOKEN and TELEGRAM_WEBHOOK_SECRET in the .env file first.');

            return self::FAILURE;
        }

        $url = $this->argument('url') ?? route('telegram.webhook');

        if (! $telegram->setWebhook($url, $secret)) {
            $this->components->error("Telegram rejected the webhook [{$url}].");

            return self::FAILURE;
        }

        $this->components->info("Webhook registered: {$url}");

        return self::SUCCESS;
    }
}

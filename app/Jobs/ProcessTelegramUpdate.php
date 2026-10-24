<?php

namespace App\Jobs;

use App\Services\Court\CourtBot;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessTelegramUpdate implements ShouldQueue
{
    use Queueable;

    /**
     * Replies are sent while the job runs, so a retry would send them twice.
     */
    public int $tries = 1;

    public int $timeout = 120;

    /**
     * @param  array<string, mixed>  $message  The "message" object of a Telegram update.
     */
    public function __construct(public array $message) {}

    public function handle(CourtBot $bot): void
    {
        $bot->handle($this->message);
    }
}

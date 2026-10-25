<?php

namespace App\Console\Commands;

use App\Services\GeminiClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('gemini:check')]
#[Description('Check that the configured Gemini API key works and explain why it does not')]
class CheckGemini extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GeminiClient $gemini): int
    {
        $problem = $gemini->diagnose();

        if ($problem !== null) {
            $this->components->error($problem);

            return self::FAILURE;
        }

        $reply = $gemini->generate("Faqat bitta so'z bilan javob bering: Salom");

        if ($reply === null) {
            $this->components->error("Model ro'yxati olindi, lekin javob yaratilmadi.");
            $this->line('  Sababi: '.($gemini->lastError() ?? "noma'lum, storage/logs/laravel.log ni qarang"));

            return self::FAILURE;
        }

        $this->components->info("Gemini ishlayapti. Javob: {$reply}");

        return self::SUCCESS;
    }
}

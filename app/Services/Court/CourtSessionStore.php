<?php

namespace App\Services\Court;

use Illuminate\Support\Facades\Cache;

/**
 * Keeps each Telegram chat's current court hearing in the cache.
 *
 * @phpstan-type CourtSession array{session_id: string, type: string, opponent_title: string, case_text: string, history: string}
 */
class CourtSessionStore
{
    private const int TTL_DAYS = 7;

    /**
     * @return CourtSession|null
     */
    public function get(int|string $chatId): ?array
    {
        $session = Cache::get($this->key($chatId));

        return is_array($session) && ! empty($session['session_id']) ? $session : null;
    }

    /**
     * @param  CourtSession  $session
     */
    public function put(int|string $chatId, array $session): void
    {
        Cache::put($this->key($chatId), $session, now()->addDays(self::TTL_DAYS));
    }

    public function forget(int|string $chatId): void
    {
        Cache::forget($this->key($chatId));
    }

    /**
     * Whether the chat is still in the hearing a slow AI reply was started for.
     */
    public function isActive(int|string $chatId, string $sessionId): bool
    {
        return ($this->get($chatId)['session_id'] ?? null) === $sessionId;
    }

    private function key(int|string $chatId): string
    {
        return "court:session:{$chatId}";
    }
}

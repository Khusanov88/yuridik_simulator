<?php

namespace App\Http\Requests\Concerns;

/**
 * For requests that may carry a browser voice recording as "audio_base64" and "audio_mime".
 */
trait HasAudioAttachment
{
    /**
     * @return array<string, array<int, string>>
     */
    protected function audioRules(): array
    {
        return [
            'audio_base64' => ['nullable', 'string', 'max:15000000'],
            'audio_mime' => ['nullable', 'string', 'starts_with:audio/', 'max:100'],
        ];
    }

    /**
     * @return array{mime_type: string, data: string}|null
     */
    public function audio(): ?array
    {
        if (! $this->filled('audio_base64')) {
            return null;
        }

        return [
            'mime_type' => $this->string('audio_mime', 'audio/webm')->toString(),
            'data' => $this->string('audio_base64')->toString(),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class CourtTurnRequest extends CourtRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'user_text' => ['required_without:audio_base64', 'nullable', 'string', 'max:10000'],
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

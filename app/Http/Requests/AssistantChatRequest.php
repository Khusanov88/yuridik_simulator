<?php

namespace App\Http\Requests;

use App\Services\LegalAssistant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssistantChatRequest extends FormRequest
{
    /**
     * The assistant is public; abuse is limited by the "ai" rate limiter.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'question' => ['required_without_all:audio_base64,file_text', 'nullable', 'string', 'max:10000'],
            'mode' => ['nullable', 'string', Rule::in(LegalAssistant::modes())],
            'file_text' => ['nullable', 'string', 'max:200000'],
            'history' => ['nullable', 'string', 'max:50000'],
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

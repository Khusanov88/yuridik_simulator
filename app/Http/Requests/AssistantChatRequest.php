<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasAudioAttachment;
use App\Services\LegalAssistant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssistantChatRequest extends FormRequest
{
    use HasAudioAttachment;

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
            ...$this->audioRules(),
            'question' => ['required_without_all:audio_base64,file_text', 'nullable', 'string', 'max:10000'],
            'mode' => ['nullable', 'string', Rule::in(LegalAssistant::modes())],
            'file_text' => ['nullable', 'string', 'max:200000'],
            'history' => ['nullable', 'string', 'max:50000'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasAudioAttachment;
use Illuminate\Contracts\Validation\ValidationRule;

class CourtTurnRequest extends CourtRequest
{
    use HasAudioAttachment;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            ...$this->audioRules(),
            'user_text' => ['required_without:audio_base64', 'nullable', 'string', 'max:10000'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\HasAudioAttachment;
use App\Services\CourtHall;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvisorRequest extends FormRequest
{
    use HasAudioAttachment;

    /**
     * The advisor is public; abuse is limited by the "ai" rate limiter.
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
            'question' => ['required_without:audio_base64', 'nullable', 'string', 'max:10000'],
            'mode' => ['nullable', 'string', Rule::in(CourtHall::ADVISOR_MODES)],
        ];
    }
}

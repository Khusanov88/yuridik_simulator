<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The hearing context the court simulator page sends with every request.
 */
class CourtRequest extends FormRequest
{
    /**
     * @var list<string>
     */
    private const array CONTEXT_FIELDS = ['court_type', 'level_name', 'level_rule', 'case_text', 'evidence_list', 'history'];

    /**
     * The simulator is public; abuse is limited by the "ai" rate limiter.
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
            'court_type' => ['nullable', 'string', 'max:200'],
            'level_name' => ['nullable', 'string', 'max:100'],
            'level_rule' => ['nullable', 'string', 'max:1000'],
            'case_text' => ['nullable', 'string', 'max:10000'],
            'evidence_list' => ['nullable', 'string', 'max:10000'],
            'history' => ['nullable', 'string', 'max:50000'],
        ];
    }

    /**
     * @return array{court_type?: string|null, level_name?: string|null, level_rule?: string|null, case_text?: string|null, evidence_list?: string|null, history?: string|null}
     */
    public function courtContext(): array
    {
        return $this->safe()->only(self::CONTEXT_FIELDS);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SummaryQueryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => [
                'required',
                'date',
            ],
            'to' => [
                'required',
                'date',
                'after:from',
            ],
            'keys' => [
                'sometimes',
                'string',
                'regex:/^[a-zA-Z0-9,_\-\s]+$/',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'from.required' => 'The from date is required.',
            'from.date' => 'The from date must be a valid date.',
            'to.required' => 'The to date is required.',
            'to.date' => 'The to date must be a valid date.',
            'to.after' => 'The to date must be after the from date.',
            'keys.regex' => 'The keys parameter must contain only alphanumeric characters, commas, underscores, hyphens, and spaces.',
        ];
    }
}
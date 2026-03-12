<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrendQueryRequest extends FormRequest
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
            'metric' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_\-\.]+$/',
            ],
            'from' => [
                'required',
                'date',
            ],
            'to' => [
                'required',
                'date',
                'after_or_equal:from',
            ],
            'bucket' => [
                'sometimes',
                Rule::in(['day', 'week', 'month']),
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
            'metric.required' => 'The metric parameter is required.',
            'metric.max' => 'The metric must not exceed 64 characters.',
            'metric.regex' => 'The metric may only contain alphanumeric characters, hyphens, underscores, and periods.',
            'from.required' => 'The from date is required.',
            'from.date' => 'The from date must be a valid date.',
            'to.required' => 'The to date is required.',
            'to.date' => 'The to date must be a valid date.',
            'to.after_or_equal' => 'The to date must be after or equal to the from date.',
            'bucket.in' => 'The bucket must be one of: day, week, month.',
        ];
    }
}
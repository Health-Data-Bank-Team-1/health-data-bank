<?php

namespace App\Http\Controllers\Researcher;

use Illuminate\Foundation\Http\FormRequest;

class CohortFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:age_min'],

            'gender' => ['nullable', 'array'],
            'gender.*' => ['string', 'max:50'],

            'location' => ['nullable', 'array'],
            'location.*' => ['string', 'max:100'],

            'goal_status' => ['nullable', 'array'],
            'goal_status.*' => ['string', 'max:50'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}

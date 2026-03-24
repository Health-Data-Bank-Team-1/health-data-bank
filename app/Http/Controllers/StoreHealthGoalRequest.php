<?php

namespace App\Http\Controllers;

use App\Models\FormField;
use Illuminate\Foundation\Http\FormRequest;

class StoreHealthGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $metricKeys = FormField::query()
            ->where('goal_enabled', true)
            ->whereIn('field_type', ['number', 'decimal'])
            ->pluck('metric_key')
            ->filter()
            ->toArray();

        return [
            'metric_key' => ['required', 'in:' . implode(',', $metricKeys)],
            'comparison_operator' => ['required', 'in:<=,>=,='],
            'target_value' => ['required', 'numeric', 'min:0'],
            'timeframe' => ['required', 'in:day,week,month'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:ACTIVE,MET,EXPIRED'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderAnalyticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasRole('provider');
    }

    public function rules(): array
    {
        return [
            'mode' => ['required', Rule::in(['group', 'participants'])],

            'metrics' => ['required', 'array', 'min:1'],
            'metrics.*' => ['required', 'string'],

            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],

            'granularity' => ['nullable', Rule::in(['day', 'week', 'month'])],

            // participants mode
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['nullable', 'uuid', 'exists:accounts,id'],

            // group mode
            'group_a' => ['nullable', 'array'],
            'group_b' => ['nullable', 'array'],

            'group_a.gender' => ['nullable', 'array'],
            'group_a.gender.*' => ['string'],

            'group_b.gender' => ['nullable', 'array'],
            'group_b.gender.*' => ['string'],

            'group_a.location' => ['nullable', 'string', 'max:255'],
            'group_b.location' => ['nullable', 'string', 'max:255'],

            'group_a.age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'group_a.age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'group_b.age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'group_b.age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('mode') === 'participants' && empty($this->input('participant_ids'))) {
                $validator->errors()->add('participant_ids', 'Please select at least one participant.');
            }

            if ($this->input('mode') === 'group' && empty($this->input('group_a'))) {
                $validator->errors()->add('group_a', 'Group A filters are required.');
            }

            foreach (['group_a', 'group_b'] as $groupKey) {
                $group = $this->input($groupKey, []);

                if (
                    isset($group['age_min'], $group['age_max']) &&
                    $group['age_min'] !== null &&
                    $group['age_max'] !== null &&
                    (int) $group['age_min'] > (int) $group['age_max']
                ) {
                    $validator->errors()->add("$groupKey.age_max", 'Age max must be greater than or equal to age min.');
                }
            }
        });
    }
}

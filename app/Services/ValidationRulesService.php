<?php

namespace App\Services;

use Illuminate\Validation\Rule;

/**
 * Centralized validation rules for the application
 * 
 * Provides reusable validation rule arrays for various
 * entities to maintain consistency across the application.
 */
class ValidationRulesService
{
    /**
     * Get validation rules for form submission creation
     *
     * @return array<string, array<string>>
     */
    public static function formSubmissionRules(): array
    {
        return [
            'form_template_id' => ['required', 'uuid', 'exists:form_templates,id'],
            'entries' => ['required', 'array', 'min:0'],
            'entries.*.field_id' => ['required', 'uuid', 'exists:form_fields,id'],
            'entries.*.value' => ['nullable', 'string'],
        ];
    }

    /**
     * Get validation rules for trend queries
     *
     * @return array<string, array<string>>
     */
    public static function trendQueryRules(): array
    {
        return [
            'metric' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Za-z0-9_\-\.]+$/',
            ],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'bucket' => ['sometimes', Rule::in(['day', 'week', 'month'])],
        ];
    }

    /**
     * Get validation rules for personal summary queries
     *
     * @return array<string, array<string>>
     */
    public static function summaryQueryRules(): array
    {
        return [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after:from'],
            'keys' => ['sometimes', 'string', 'regex:/^[a-zA-Z0-9,_\-\s]+$/'],
        ];
    }

    /**
     * Get validation rules for form template creation
     *
     * @return array<string, array<string>>
     */
    public static function formTemplateStoreRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'schema' => ['required', 'array'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get validation rules for form template updates
     *
     * @return array<string, array<string>>
     */
    public static function formTemplateUpdateRules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'schema' => ['sometimes', 'array'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get validation rules for form template rejection
     *
     * @return array<string, array<string>>
     */
    public static function formTemplateRejectRules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:255', 'min:10'],
        ];
    }

    /**
     * Get validation rules for health entries
     *
     * @return array<string, array<string>>
     */
    public static function healthEntryRules(): array
    {
        return [
            'submission_id' => ['required', 'uuid', 'exists:form_submissions,id'],
            'account_id' => ['required', 'uuid', 'exists:accounts,id'],
            'encrypted_values' => ['required', 'array'],
            'timestamp' => ['required', 'date'],
        ];
    }

    /**
     * Get validation messages for common fields
     *
     * @return array<string, string>
     */
    public static function commonMessages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'uuid' => 'The :attribute must be a valid UUID.',
            'exists' => 'The selected :attribute does not exist.',
            'date' => 'The :attribute must be a valid date.',
            'string' => 'The :attribute must be a string.',
            'array' => 'The :attribute must be an array.',
            'max' => 'The :attribute must not exceed :max characters.',
            'min' => 'The :attribute must be at least :min characters.',
        ];
    }
}
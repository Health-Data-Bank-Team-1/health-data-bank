<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormSubmissionRequest extends FormRequest
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
            'form_template_id' => ['required', 'uuid', 'exists:form_templates,id'],
            'entries' => ['required', 'array', 'min:0'],
            'entries.*.field_id' => ['required', 'uuid', 'exists:form_fields,id'],
            'entries.*.value' => ['nullable', 'string'],
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
            'form_template_id.required' => 'The form template ID is required.',
            'form_template_id.uuid' => 'The form template ID must be a valid UUID.',
            'form_template_id.exists' => 'The selected form template does not exist.',
            'entries.required' => 'At least an empty entries array is required.',
            'entries.array' => 'Entries must be an array.',
            'entries.*.field_id.required' => 'Each entry must have a field ID.',
            'entries.*.field_id.uuid' => 'Each field ID must be a valid UUID.',
            'entries.*.field_id.exists' => 'The selected field does not exist.',
        ];
    }
}
<?php

namespace App\Livewire;

use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\HealthEntry;
use App\Services\SubmissionFlaggingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class FormRenderer extends Component
{

    public FormTemplate $form;

    public array $entries = [];

    public $formId;

    public function mount(FormTemplate $form)
    {
        abort_unless($form->approval_status === 'approved', 404);

        $this->form = $form->load('fields');
        $this->formId = $form->id;

        foreach ($this->form->fields as $field) {
            $this->entries[$field->id] = $field->field_type === 'Checkbox' ? [] : null;
        }
    }

    protected function rules()
    {
        $rules = [];

        foreach ($this->form->fields as $field) {
            $rules["entries.{$field->id}"] = $this->normalizeRules($field->validation_rules);
        }

        return $rules;
    }

    protected function normalizeRules($rawRules): array
    {
        if (empty($rawRules)) {
            return [];
        }

        if (is_string($rawRules)) {
            $decoded = json_decode($rawRules, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rawRules = $decoded;
            } else {
                return array_filter(array_map('trim', explode('|', $rawRules)));
            }
        }

        if (!is_array($rawRules)) {
            return [];
        }

        $normalized = [];

        foreach ($rawRules as $key => $value) {
            if (is_int($key)) {
                if (is_string($value)) {
                    $normalized[] = trim($value);
                }
                continue;
            }

            if ($value === true) {
                $normalized[] = $key;
                continue;
            }

            if ($value === false || $value === null || $value === '') {
                continue;
            }

            $normalized[] = "{$key}:{$value}";
        }

        return $normalized;
    }

    protected function validationAttributes()
    {
        $attributes = [];

        foreach ($this->form->fields as $field) {
            $attributes["entries.{$field->id}"] = $field->label;
        }

        return $attributes;
    }

    public function submit()
    {
        logger()->info($this->rules());
        $this->validate();

        $user = Auth::user();

        if (! $user || ! $user->account_id) {
            session()->flash('error', 'User is not linked to an account.');
            return;
        }

        $submission = FormSubmission::create([
            'id' => Str::uuid(),
            'account_id' => $user->account_id,
            'form_template_id' => $this->form->id,
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ]);

        foreach ($this->form->fields as $field) {
            $value = $this->entries[$field->id] ?? null;

            HealthEntry::create([
                'id' => Str::uuid(),
                'submission_id' => $submission->id,
                'account_id' => $user->account_id,
                'timestamp' => now(),
                'encrypted_values' => [
                    'field_id' => $field->id,
                    'metric_key' => $field->metric_key,
                    'field_label' => $field->label,
                    'field_type' => $field->field_type,
                    'value' => $value,
                ],
            ]);
        }

        app(SubmissionFlaggingService::class)->evaluate($submission);

        return redirect()
            ->to('/user/form-select')
            ->with('flash.banner', 'Form submitted successfully!')
            ->with('flash.bannerStyle', 'success');
    }

    public function render()
    {
        return view('livewire.form-renderer')
            ->layout('layouts.user');
    }
}

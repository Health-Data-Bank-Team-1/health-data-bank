<?php

namespace App\Livewire;

use App\Models\FormTemplate;
use Livewire\Component;

class FormRenderer extends Component
{
    public $showDescription = false;

    public FormTemplate $form;

    public array $entries = [];

    public $formId;

    public function mount(FormTemplate $form)
    {
        abort_unless($form->approval_status === 'approved', 404);

        $this->form = $form->load('fields');
        $this->formId = $form->id;

        foreach ($this->form->fields as $field) {
            if ($field->field_type === 'Checkbox') {
                $this->entries[$field->id] = [];
            } else {
                $this->entries[$field->id] = null;
            }
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
        if (is_string($rawRules)) {
            return array_filter(array_map('trim', explode('|', $rawRules)));
        }

        if (!is_array($rawRules)) {
            return [];
        }

        $normalized = [];

        foreach ($rawRules as $key => $value) {
            if (is_int($key)) {
                $normalized[] = $value;
                continue;
            }

            if ($value === true) {
                $normalized[] = $key;
            } elseif ($value !== false && $value !== null && $value !== '') {
                $normalized[] = "{$key}:{$value}";
            }
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
        $this->validate();

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

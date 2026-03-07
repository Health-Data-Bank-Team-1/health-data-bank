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
            $rules["entries.{$field->id}"] = $field->validation_rules;
        }

        return $rules;
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
        $validated = $this->validate();

        // replace loop with submission endpoint, here to simulate loading
        for ($i=1;$i<3;++$i) {
            sleep(1);
        }

        return redirect()
            ->to('/user-form-select')
            ->with('flash.banner', 'Form submitted successfully!')
            ->with('flash.bannerStyle', 'success');
    }

    public function render()
    {
        return view('livewire.form-renderer')
            ->layout('layouts.user');
    }
}

<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\FormTemplate;
use Illuminate\Database\Seeder;

class FormTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $nameform = FormTemplate::create([
            'title' => 'Name Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'Name form for names',
            'schema' => '{}',
        ]);

        FormField::create([
            'form_template_id' => $nameform->id,
            'label' => 'Full Name',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);

        FormField::create([
            'form_template_id' => $nameform->id,
            'label' => 'Email Address',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'email'],
        ]);

        $radioform = FormTemplate::create([
            'title' => 'Radio Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'for testing radio button',
            'schema' => '{}',
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Full Name',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Email Address',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'email'],
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Button Work',
            'field_type' => 'RadioButton',
            'validation_rules' => ['required'],
            'options' => ['yes', 'maybe', 'no'],
        ]);

        $checkboxform = FormTemplate::create([
            'title' => 'Checkbox Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'for testing checkbox options',
            'schema' => '{}',
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Full Name',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'string', 'max:255'],
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Email Address',
            'field_type' => 'Text',
            'validation_rules' => ['required', 'email'],
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Preferences',
            'field_type' => 'Checkbox',
            'validation_rules' => ['required'],
            'options' => ['Option A', 'Option B', 'Option C'],
        ]);

        $dateform = FormTemplate::create([
            'title' => 'Date Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'for testing date input',
            'schema' => '{}',
        ]);

        FormField::create([
            'form_template_id' => $dateform->id,
            'label' => 'Appointment Date',
            'field_type' => 'Date',
            'validation_rules' => ['required'],
        ]);

        $numberform = FormTemplate::create([
            'title' => 'Number Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'for testing number input',
            'schema' => '{}',
        ]);

        FormField::create([
            'form_template_id' => $numberform->id,
            'label' => 'Age',
            'field_type' => 'Number',
            'validation_rules' => ['required', 'integer', 'min:0'],
        ]);
    }
}

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
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $nameform->id,
            'label' => 'Full Name',
            'help_text' => 'Enter your full name',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'string', 'max:255'],
            'display_order' => 1,
        ]);

        FormField::create([
            'form_template_id' => $nameform->id,
            'label' => 'Email Address',
            'help_text' => 'Enter your email address',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'email'],
            'display_order' => 2,
        ]);

        $radioform = FormTemplate::create([
            'title' => 'Radio Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'For testing radio button',
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Full Name',
            'help_text' => 'Enter your full name',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'string', 'max:255'],
            'display_order' => 1,
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Email Address',
            'help_text' => 'Enter your email address',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'email'],
            'display_order' => 2,
        ]);

        FormField::create([
            'form_template_id' => $radioform->id,
            'label' => 'Button Work',
            'help_text' => 'Choose one option',
            'field_type' => 'radiobutton',
            'is_required' => true,
            'validation_rules' => ['required'],
            'options' => ['yes', 'maybe', 'no'],
            'display_order' => 3,
        ]);

        $checkboxform = FormTemplate::create([
            'title' => 'Checkbox Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'For testing checkbox options',
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Full Name',
            'help_text' => 'Enter your full name',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'string', 'max:255'],
            'display_order' => 1,
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Email Address',
            'help_text' => 'Enter your email address',
            'field_type' => 'text',
            'is_required' => true,
            'validation_rules' => ['required', 'email'],
            'display_order' => 2,
        ]);

        FormField::create([
            'form_template_id' => $checkboxform->id,
            'label' => 'Preferences',
            'help_text' => 'Select all that apply',
            'field_type' => 'checkbox',
            'is_required' => true,
            'validation_rules' => ['required'],
            'options' => ['Option A', 'Option B', 'Option C'],
            'display_order' => 3,
        ]);

        $dateform = FormTemplate::create([
            'title' => 'Date Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'For testing date input',
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $dateform->id,
            'label' => 'Appointment Date',
            'help_text' => 'Select an appointment date',
            'field_type' => 'date',
            'is_required' => true,
            'validation_rules' => ['required'],
            'display_order' => 1,
        ]);

        $numberform = FormTemplate::create([
            'title' => 'Number Form',
            'version' => 1,
            'approval_status' => 'approved',
            'description' => 'For testing number input',
            'schema' => [],
        ]);

        FormField::create([
            'form_template_id' => $numberform->id,
            'label' => 'Age',
            'help_text' => 'Enter your age',
            'field_type' => 'number',
            'is_required' => true,
            'validation_rules' => ['required', 'integer', 'min:0'],
            'display_order' => 1,
        ]);
    }
}

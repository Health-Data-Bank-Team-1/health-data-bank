<?php

namespace Database\Factories;

use App\Models\FormSubmission;
use App\Models\Account;
use App\Models\FormTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormSubmissionFactory extends Factory
{
    protected $model = FormSubmission::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'account_id' => Account::factory(),
            'form_template_id' => FormTemplate::factory(),
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
        ];
    }
}
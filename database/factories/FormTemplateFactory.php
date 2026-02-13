<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FormTemplate;

class FormTemplateFactory extends Factory
{
    protected $model = FormTemplate::class;

    public function definition(): array
    {
        return [
            'version' => 1,
            'status' => 'draft', //default
            'description' => $this->faker->sentence(),
            'approval_status' => 'pending', //default
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }
}

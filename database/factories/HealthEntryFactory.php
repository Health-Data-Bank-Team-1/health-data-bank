<?php

namespace Database\Factories;

use App\Models\HealthEntry;
use App\Models\Account;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class HealthEntryFactory extends Factory
{
    protected $model = HealthEntry::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'submission_id' => FormSubmission::factory(),
            'timestamp' => $this->faker->dateTime(),
            'encrypted_values' => [
                'heart_rate' => $this->faker->numberBetween(60, 100),
                'blood_pressure' => $this->faker->numberBetween(90, 140),
            ],
        ];
    }
}
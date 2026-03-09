<?php

namespace Database\Factories;

use App\Models\HealthEntry;
use App\Models\Account;
use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HealthEntryFactory extends Factory
{
    protected $model = HealthEntry::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'submission_id' => null,
            'account_id' => Account::factory(),
            'timestamp' => now()->subMinutes($this->faker->numberBetween(0, 60 * 24 * 14)),
            'encrypted_values' => [
                'bp' => $this->faker->numberBetween(100, 150),
                'hr' => $this->faker->numberBetween(55, 110),
            ],
        ];
    }
}

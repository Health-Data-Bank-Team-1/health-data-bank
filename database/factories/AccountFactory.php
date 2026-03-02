<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'account_type' => $this->faker->randomElement(['User', 'Researcher', 'HealthcareProvider', 'Admin']),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'status' => 'ACTIVE',
        ];
    }

    public function deactivated()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'DEACTIVATED',
            ];
        });
    }
}
<?php

namespace Database\Factories;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),

            // MUST match enum values exactly
            'account_type' => 'User',

            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'location' => fake()->randomElement(['PEI', 'NS', 'NB']),

            // MUST match enum values exactly
            'status' => 'ACTIVE',
        ];
    }

    public function user(): static
    {
        return $this->state(fn () => ['account_type' => 'User']);
    }

    public function researcher(): static
    {
        return $this->state(fn () => ['account_type' => 'Researcher']);
    }

    public function healthcareProvider(): static
    {
        return $this->state(fn () => ['account_type' => 'HealthcareProvider']);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['account_type' => 'Admin']);
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'ACTIVE']);
    }

    public function deactivated(): static
    {
        return $this->state(fn () => ['status' => 'DEACTIVATED']);
    }
}

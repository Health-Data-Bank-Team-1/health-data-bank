<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'actor_id' => Account::factory(),
            'action_type' => $this->faker->word(),
            'timestamp' => $this->faker->dateTime(),
        ];
    }
}
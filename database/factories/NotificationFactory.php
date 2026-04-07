<?php

namespace Database\Factories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'account_id' => \App\Models\Account::factory(),
            'type' => $this->faker->randomElement(['alert', 'reminder', 'info']),
            'message' => $this->faker->sentence(),
            'status' => 'unread',
        ];
    }
}

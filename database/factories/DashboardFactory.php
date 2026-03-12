<?php

namespace Database\Factories;

use App\Models\Dashboard;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class DashboardFactory extends Factory
{
    protected $model = Dashboard::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
        ];
    }
}
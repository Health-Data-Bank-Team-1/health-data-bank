<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AggregatedData;
use App\Models\Report;

class AggregatedDataFactory extends Factory
{
    protected $model = AggregatedData::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),

            'metrics' => [
                'weight' => $this->faker->numberBetween(120, 400),
                'meals_ate' => $this->faker->numberBetween(1, 4),
                'heart_rate' => $this->faker->numberBetween(70, 190),
                'days_slept' => $this->faker->numberBetween(1, 6),
            ]
        ];
    }
}

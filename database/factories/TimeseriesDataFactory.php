<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\TimeseriesData;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeseriesDataFactory extends Factory
{
    protected $model = TimeseriesData::class;

    public function definition(): array
    {
        $metric = $this->faker->randomElement(['weight', 'heart_rate', 'sleep_hours', 'steps']);

        $points = [];
        $date = now()->subDays(29)->startOfDay();
        for ($i = 0; $i < 30; $i++) {
            $points[] = [
                'bucket_start' => $date->copy()->addDays($i)->toIso8601String(),
                'count' => $this->faker->numberBetween(1, 20),
                'min' => $this->faker->randomFloat(2, 40, 200),
                'max' => $this->faker->randomFloat(2, 200, 400),
                'avg' => $this->faker->randomFloat(2, 60, 300),
                'latest' => $this->faker->randomFloat(2, 60, 300),
                'latest_at' => $date->copy()->addDays($i)->toIso8601String(),
            ];
        }

        return [
            'report_id' => Report::factory(),
            'metric' => $metric,
            'bucket' => 'day',
            'points' => $points,
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Account;
use App\Models\Report;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'researcher_id' => Account::factory()->researcher(),

            'report_type' => 'Aggregated',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'researcher_id' => Account::factory(),
            'report_type' => $this->faker->randomElement(['Aggregated', 'Comparative']),
        ];
    }
}
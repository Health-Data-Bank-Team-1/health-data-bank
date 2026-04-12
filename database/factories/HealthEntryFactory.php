<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\FormTemplate;
use App\Models\HealthEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class HealthEntryFactory extends Factory
{
    protected $model = HealthEntry::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'account_id' => null,
            'submission_id' => null,
            'timestamp' => now()->subMinutes($this->faker->numberBetween(0, 60 * 24 * 14)),
            'encrypted_values' => [
                'bp' => $this->faker->numberBetween(100, 150),
                'hr' => $this->faker->numberBetween(55, 110),
            ],
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (HealthEntry $entry) {
            if (!$entry->account_id) {
                $entry->account_id = Account::factory()->create()->id;
            }

            if (!$entry->submission_id) {
                $submission = FormSubmission::factory()->create([
                    'account_id' => $entry->account_id,
                    'form_template_id' => FormTemplate::factory(),
                ]);

                $entry->submission_id = $submission->id;
            }
        });
    }
}

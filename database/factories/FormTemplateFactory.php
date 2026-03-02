<?php

namespace Database\Factories;

use App\Models\FormTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormTemplateFactory extends Factory
{
    protected $model = FormTemplate::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'version' => 1,
            'status' => $this->faker->randomElement(['DRAFT', 'PUBLISHED', 'ARCHIVED']),
            'description' => $this->faker->sentence(),
            'created_at' => now(),
        ];
    }

    /**
     * Indicate that the template is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'PUBLISHED',
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Exceptions\CohortSuppressedException;
use App\Services\HealthMetricRegistry;
use App\Services\PersonalComparisonService;
use Livewire\Component;

class PersonalComparison extends Component
{
    public $metric_key;

    public $from;

    public $to;

    public $gender;

    public $location;

    public $age_min;

    public $age_max;

    public array $metricOptions = [];

    public $result = null;

    protected PersonalComparisonService $service;

    public function boot(PersonalComparisonService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $registry = app(HealthMetricRegistry::class);

        $this->metricOptions = collect($registry->all())
            ->filter(fn (array $meta) => ($meta['type'] ?? null) === 'number')
            ->mapWithKeys(fn (array $meta, string $key) => [$key => $meta['label']])
            ->toArray();

        $this->metric_key = array_key_first($this->metricOptions);
        $this->from = now()->subMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function compare(): void
    {
        $this->validate(
            [
                'metric_key' => ['required', 'string', 'in:'.implode(',', array_keys($this->metricOptions))],
                'from' => ['required', 'date'],
                'to' => ['required', 'date', 'after:from'],
                'gender' => ['nullable', 'string'],
                'location' => ['nullable', 'string'],
                'age_min' => ['nullable', 'integer', 'min:0'],
                'age_max' => ['nullable', 'integer', 'min:0', 'gte:age_min'],
            ],
            [
                'metric_key.required' => 'Please select a metric.',
                'metric_key.in' => 'The selected metric is invalid.',
                'from.required' => 'A start date is required.',
                'from.date' => 'The start date must be a valid date.',
                'to.required' => 'An end date is required.',
                'to.date' => 'The end date must be a valid date.',
                'to.after' => 'The end date must be after the start date.',
                'gender.string' => 'The gender filter must be a text value.',
                'location.string' => 'The location filter must be a text value.',
                'age_min.integer' => 'The minimum age must be a whole number.',
                'age_min.min' => 'The minimum age cannot be negative.',
                'age_max.integer' => 'The maximum age must be a whole number.',
                'age_max.min' => 'The maximum age cannot be negative.',
                'age_max.gte' => 'The maximum age must be greater than or equal to the minimum age.',
            ]
        );

        try {
            $this->result = $this->service->compare(
                $this->metric_key,
                $this->from,
                $this->to,
                [
                    'gender' => $this->gender,
                    'location' => $this->location,
                    'age_min' => $this->age_min,
                    'age_max' => $this->age_max,
                ]
            );
        } catch (CohortSuppressedException $e) {
            $this->result = [
                'metric_key' => $this->metric_key,
                'user_value' => null,
                'group' => [
                    'is_suppressed' => true,
                    'count' => null,
                    'avg' => null,
                    'message' => 'Group too small to display aggregate results.',
                ],
            ];
        }
    }

    public function render()
    {
        return view('livewire.personal-comparison')
            ->layout('layouts.user');
    }
}

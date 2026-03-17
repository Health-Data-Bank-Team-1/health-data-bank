<?php

namespace App\Livewire;

use App\Exceptions\CohortSuppressedException;
use App\Models\FormField;
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
        $this->metricOptions = FormField::query()
            ->where('goal_enabled', true)
            ->whereIn('field_type', ['number', 'decimal'])
            ->orderBy('label')
            ->pluck('label', 'metric_key')
            ->toArray();

        $this->metric_key = array_key_first($this->metricOptions);
        $this->from = now()->subMonth()->toDateString();
        $this->to = now()->toDateString();
    }

    public function compare(): void
    {
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
            )->toArray();
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

<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CompareGroup extends Component
{
    public $selectedMetric = 'alcohol_consumption';

    public array $metricOptions = [
        'alcohol_consumption' => 'Alcohol Consumption',
        'sleep_hours' => 'Sleep Hours',
        'stress_level' => 'Stress Level',
        'exercise_frequency' => 'Exercise Frequency',
    ];

    public $comparisonRows = [];

    public function mount()
    {
        $this->loadComparison();
    }

    public function updatedSelectedMetric()
    {
        $this->loadComparison();
    }

    private function loadComparison()
    {
        $this->comparisonRows = [
            [
                'metric' => $this->metricOptions[$this->selectedMetric] ?? $this->selectedMetric,
                'your_value' => null,
                'group_average' => null,
                'difference' => null,
            ]
        ];
    }

    public function render()
    {
        return view('livewire.compare-group');
    }
}

<?php

namespace App\Livewire\Researcher;

use App\Models\FormField;
use Livewire\Component;

class ResearcherReportGenerator extends Component
{
    public string $from = '';
    public string $to = '';
    public ?string $gender = null;
    public ?string $location = null;
    public ?int $age_min = null;
    public ?int $age_max = null;
    public array $metricOptions = [];

    public function mount(): void
    {
        $this->from = now()->subMonth()->toDateString();
        $this->to = now()->toDateString();

        $this->metricOptions = FormField::query()
            ->where('goal_enabled', true)
            ->whereIn('field_type', ['number', 'integer', 'decimal'])
            ->orderBy('label')
            ->pluck('label', 'metric_key')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([]);
    }
}

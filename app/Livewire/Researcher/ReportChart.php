<?php

namespace App\Livewire\Researcher;

use Livewire\Component;

class ReportChart extends Component
{
    public string $chartId;

    public array $aggregateData = [];

    public array $timeseriesData = [];

    public string $viewMode = 'aggregate';

    public array $chartLabels = [];

    public array $chartValues = [];

    public array $timeseriesDatasets = [];

    public string $chartLabel = '';

    public array $aggregateMetrics = [];

    public bool $hasAggregate = false;

    public bool $hasTimeseries = false;

    public function mount(array $aggregateData = [], array $timeseriesData = [])
    {
        $this->chartId = 'reportChart_'.uniqid();
        $this->aggregateData = $aggregateData;
        $this->timeseriesData = $timeseriesData;
        $this->hasAggregate = ! empty($aggregateData);
        $this->hasTimeseries = ! empty($timeseriesData);
        $this->buildChartData();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
        $this->buildChartData();
        $this->dispatch('report-chart-updated');
    }

    protected function buildChartData(): void
    {
        if ($this->viewMode === 'timeseries' && ! empty($this->timeseriesData)) {
            $allLabels = collect();
            $this->timeseriesDatasets = [];

            foreach ($this->timeseriesData as $metric => $ts) {
                $points = $ts['points'] ?? [];
                $labels = collect($points)
                    ->pluck('bucket_start')
                    ->map(fn ($d) => \Carbon\Carbon::parse($d)->format('M j, Y'))
                    ->values();
                $allLabels = $allLabels->merge($labels);

                $this->timeseriesDatasets[] = [
                    'label' => $ts['metric'] ?? $metric,
                    'values' => collect($points)
                        ->pluck('avg')
                        ->map(fn ($v) => $v !== null ? round($v, 2) : null)
                        ->values()
                        ->toArray(),
                    'labels' => $labels->toArray(),
                ];
            }

            $this->chartLabels = $allLabels->unique()->values()->toArray();
            $this->chartValues = [];
            $this->chartLabel = 'Timeseries (daily avg)';
        } else {
            $this->aggregateMetrics = [];
            foreach ($this->aggregateData as $key => $value) {
                if (is_array($value) && isset($value['avg'])) {
                    $this->aggregateMetrics[$key] = round($value['avg'], 2);
                } elseif (is_numeric($value)) {
                    $this->aggregateMetrics[$key] = round((float) $value, 2);
                }
            }
            $this->chartLabel = 'Aggregated Metrics';
        }
    }

    public function render()
    {
        return view('livewire.researcher.report-chart');
    }
}

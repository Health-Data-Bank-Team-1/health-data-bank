<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ReportRenderer extends Component
{
    public Report $report;

    public array $aggregateData = [];

    public array $metrics = [];
    public array $displayMetrics = [];

    public array $timeseriesRows = [];

    public array $notes = [];

    public function mount(Report $report): void
    {
        $this->report = $report;

        $aggregatedData = $report->aggregatedData ?? [];
        $metricMap = [];

        foreach ($aggregatedData as $data) {
            $metrics = is_array($data)
                ? ($data['metrics'] ?? [])
                : ($data->metrics ?? []);

            if (!is_array($metrics)) {
                continue;
            }

            foreach ($metrics as $key => $value) {
                $metricMap[$key] = $value;
            }
        }

        $tsData = $report->timeseriesData ?? [];

        if ($tsData instanceof \Illuminate\Support\Collection) {
            $tsData = $tsData->toArray();
        }

        $this->timeseriesRows = is_array($tsData) ? $tsData : [];

        $this->notes = $report->updates()->latest()->get()->toArray();
    }

    public function render()
    {
        return view('livewire.researcher.report-renderer');
    }
}

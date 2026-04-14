<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ReportRenderer extends Component
{
    public Report $report;

    public array $aggregateData = [];

    public array $metrics = [];

    public array $timeseriesRows = [];

    public array $notes = [];

    public function mount(Report $report): void
    {
        $this->report = $report;

        $data = $report->aggregatedData ?? [];

        if ($data instanceof \Illuminate\Support\Collection) {
            $data = $data->toArray();
        }

        $this->aggregateData = is_array($data) ? $data : [];

        foreach ($this->aggregateData as $row) {
            if (is_array($row) && isset($row['metrics']) && is_array($row['metrics'])) {
                $this->metrics = $row['metrics'];
                break;
            }

            if (is_object($row) && isset($row->metrics) && is_array($row->metrics)) {
                $this->metrics = $row->metrics;
                break;
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

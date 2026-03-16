<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ReportRenderer extends Component
{
    public Report $report;
    public $aggregateData;
    public $metrics;

    public function mount(Report $report)
    {
        $this->report = $report;
        $this->aggregateData = $report->aggregatedData;

        foreach ($this->aggregateData as $data) {
            $this->metrics = $data->metrics;
        }
    }

    public function render()
    {
        return view('livewire.researcher.report-renderer');
    }
}

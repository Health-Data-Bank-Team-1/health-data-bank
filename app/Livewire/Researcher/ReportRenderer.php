<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ReportRenderer extends Component
{
    public Report $report;

    public array $metrics = [];
    public array $displayMetrics = [];

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

        $this->metrics = $metricMap;

        foreach ($metricMap as $key => $value) {
            $this->displayMetrics[] = [
                'key' => (string) $key,
                'label' => str_replace('_', ' ', ucfirst((string) $key)),
                'value' => is_array($value) ? json_encode($value) : $value,
            ];
        }
    }

    public function render()
    {
        return view('livewire.researcher.report-renderer');
    }
}

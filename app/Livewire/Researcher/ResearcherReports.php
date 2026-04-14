<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use Livewire\Component;

class ResearcherReports extends Component
{
    public $currReport = null;

    public string $searchId = '';

    protected $listeners = ['reportSelected' => 'selectReport'];

    public function mount(Report $report)
    {
        $this->currReport = $report;
    }

    public function selectReport(string $reportId): void
    {
        $report = Report::find($reportId);
        if ($report) {
            $this->currReport = $report;
        }
    }

    public function searchById(): void
    {
        $this->validate([
            'searchId' => ['required', 'string'],
        ]);

        $report = Report::where('id', $this->searchId)->first();

        if ($report) {
            $this->currReport = $report;
        } else {
            $this->addError('searchId', 'No report found with that ID.');
        }
    }

    public function render()
    {
        return view('livewire.researcher.reports')
            ->layout('layouts.researcher');
    }
}

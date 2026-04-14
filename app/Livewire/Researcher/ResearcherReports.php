<?php

namespace App\Livewire\Researcher;

use App\Models\Report;
use App\Models\ReportUpdate;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResearcherReports extends Component
{
    public $currReport = null;

    public string $searchId = '';

    public string $noteContent = '';

    public bool $showNoteForm = false;

    public int $noteCount = 0;

    protected $listeners = ['reportSelected' => 'selectReport'];

    public function mount(Report $report)
    {
        $this->currReport = $report;
        $this->noteCount = $report->updates()->count();
    }

    public function selectReport(string $reportId): void
    {
        $report = Report::find($reportId);
        if ($report) {
            $this->currReport = $report;
            $this->showNoteForm = false;
            $this->noteContent = '';
            $this->noteCount = $report->updates()->count();
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
            $this->showNoteForm = false;
            $this->noteContent = '';
            $this->noteCount = $report->updates()->count();
        } else {
            $this->addError('searchId', 'No report found with that ID.');
        }
    }

    public function addNote(): void
    {
        $this->validate([
            'noteContent' => ['required', 'string', 'max:5000'],
        ]);

        if (! $this->currReport) {
            return;
        }

        ReportUpdate::create([
            'report_id' => $this->currReport->id,
            'researcher_account_id' => Auth::user()->account_id,
            'content' => $this->noteContent,
        ]);

        AuditLogger::log(
            'researcher_report_note_added',
            ['reporting', 'researcher', 'outcome:success'],
            null,
            [],
            [
                'report_id' => $this->currReport->id,
            ]
        );

        $this->currReport = Report::find($this->currReport->id);
        $this->noteContent = '';
        $this->showNoteForm = false;
        $this->noteCount++;
    }

    public function exportCsv()
    {
        if (! $this->currReport) {
            $this->addError('searchId', 'No report selected.');

            return;
        }

        return redirect()->route('researcher.reports.export', [
            'report' => $this->currReport->id,
        ]);
    }

    public function render()
    {
        return view('livewire.researcher.reports')
            ->layout('layouts.researcher');
    }
}

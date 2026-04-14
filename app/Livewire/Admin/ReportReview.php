<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use App\Services\AuditLogger;
use Livewire\Component;

class ReportReview extends Component
{
    public string $search = '';
    public ?string $selectedReportId = null;

    public function selectReport(string $reportId): void
    {
        $this->selectedReportId = $reportId;
    }

    public function deleteSelectedReport(): void
    {
        abort_unless($this->selectedReportId, 404, 'No report selected.');

        $report = Report::query()->findOrFail($this->selectedReportId);

        $reportId = $report->id;

        $report->delete();

        AuditLogger::log(
            'admin_report_deleted',
            ['admin', 'resource:report', 'outcome:success'],
            null,
            [],
            [
                'report_id' => $reportId,
            ]
        );

        session()->flash('message', 'Report deleted successfully.');

        $this->selectedReportId = null;
    }

    public function getReportsProperty()
    {
        $query = Report::query()->latest();

        if (trim($this->search) !== '') {
            $term = trim($this->search);

            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%");

                if ($this->columnExists('reports', 'title')) {
                    $q->orWhere('title', 'like', "%{$term}%");
                }

                if ($this->columnExists('reports', 'name')) {
                    $q->orWhere('name', 'like', "%{$term}%");
                }

                if ($this->columnExists('reports', 'description')) {
                    $q->orWhere('description', 'like', "%{$term}%");
                }
            });
        }

        return $query->get();
    }

    public function getSelectedReportProperty(): ?Report
    {
        if (!$this->selectedReportId) {
            return null;
        }

        return Report::query()->find($this->selectedReportId);
    }

    protected function columnExists(string $table, string $column): bool
    {
        static $cache = [];

        $key = $table . '.' . $column;

        if (!array_key_exists($key, $cache)) {
            $cache[$key] = \Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }

    public function render()
    {
        return view('livewire.admin.report-review', [
            'reports' => $this->reports,
            'selectedReport' => $this->selectedReport,
        ])
            ->layout('layouts.admin')
            ->layoutData([
                'header' => 'Report Review',
            ]);
    }
}

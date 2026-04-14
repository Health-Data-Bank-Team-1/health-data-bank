<?php

namespace App\Livewire\Researcher;

use App\Services\AggregatedMetricsService;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\TrendCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ResearcherReportGenerator extends Component
{
    public ?int $min_age = null;

    public ?int $max_age = null;

    public ?string $gender = null;

    public ?string $from = null;

    public ?string $to = null;

    public string $metricsInput = '';

    public ?int $estimatedSize = null;

    public array $reportResults = [];

    public array $summaryStats = [];

    public array $timeseriesResults = [];

    public ?string $reportMessage = null;

    public function estimatePopulation(): void
    {
        $filters = $this->buildFilters();

        $accountIds = app(CohortFilterBuilder::class)
            ->build($filters)
            ->pluck('id')
            ->all();

        $this->estimatedSize = count($accountIds);
    }

    public function generateReport(): void
    {
        $validated = $this->validate([
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'metricsInput' => ['required', 'string'],
        ]);

        if (! is_null($validated['min_age']) && ! is_null($validated['max_age']) && $validated['min_age'] > $validated['max_age']) {
            $this->addError('max_age', 'Max age must be greater than or equal to min age.');

            return;
        }

        $filters = $this->buildFilters();

        $accountIds = app(CohortFilterBuilder::class)
            ->build($filters)
            ->pluck('id')
            ->all();

        $count = count($accountIds);
        $this->estimatedSize = $count;

        $metrics = collect(explode(',', $validated['metricsInput']))
            ->map(fn ($metric) => trim($metric))
            ->filter()
            ->values()
            ->all();

        if (empty($metrics)) {
            $this->addError('metricsInput', 'Please enter at least one metric.');

            return;
        }

        $fromDate = Carbon::parse($validated['from'])->startOfDay();
        $toDate = Carbon::parse($validated['to'])->endOfDay();

        $results = app(AggregatedMetricsService::class)->aggregateForCohort(
            $accountIds,
            $fromDate,
            $toDate,
            $metrics
        );

        $timeseries = [];
        foreach ($metrics as $metric) {
            $timeseries[$metric] = app(TrendCalculationService::class)->timeSeriesForCohort(
                $accountIds,
                $metric,
                $fromDate,
                $toDate,
                'day'
            );
        }

        $this->reportResults = $results;
        $this->timeseriesResults = $timeseries;
        $this->summaryStats = [
            'population_size' => $count,
            'from' => $validated['from'],
            'to' => $validated['to'],
            'metrics' => $metrics,
            'filters' => $filters,
        ];
        $this->reportMessage = 'Anonymous report generated successfully.';

        AuditLogger::log(
            'researcher_aggregated_report_generated',
            ['researcher', 'success'],
            null,
            [],
            [
                'researcher_account_id' => Auth::user()->account_id,
                'population_size' => $count,
                'from' => $validated['from'],
                'to' => $validated['to'],
                'metrics' => $metrics,
                'filters' => $filters,
            ]
        );
    }

    protected function buildFilters(): array
    {
        return array_filter([
            'account_type' => 'User',
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'gender' => $this->gender,
        ], fn ($value) => ! is_null($value) && $value !== '');
    }

    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Report Generator',
            ]);
    }
}

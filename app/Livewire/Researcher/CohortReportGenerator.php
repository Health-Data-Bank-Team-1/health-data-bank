<?php

namespace App\Livewire\Researcher;

use App\Exceptions\CohortSuppressedException;
use App\Models\ResearcherCohort;
use App\Services\AggregatedMetricsService;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Services\TrendCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class CohortReportGenerator extends Component
{
    public ?string $selectedCohortId = null;

    public string $name = '';

    public string $purpose = '';

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

    public array $savedCohorts = [];

    public function mount(): void
    {
        $this->loadSavedCohorts();
    }

    public function updatedSelectedCohortId(?string $value): void
    {
        if ($value) {
            $cohort = ResearcherCohort::find($value);
            if ($cohort) {
                $filters = $cohort->filters_json ?? [];
                $this->min_age = $filters['min_age'] ?? null;
                $this->max_age = $filters['max_age'] ?? null;
                $this->gender = $filters['gender'] ?? null;
                $this->estimatedSize = null;
                $this->reportResults = [];
                $this->summaryStats = [];
                $this->timeseriesResults = [];
                $this->reportMessage = null;
            }
        } else {
            $this->reset(['min_age', 'max_age', 'gender', 'estimatedSize', 'reportResults', 'summaryStats', 'timeseriesResults', 'reportMessage']);
        }
    }

    public function estimatePopulation(): void
    {
        $filters = $this->buildFilters();

        $this->estimatedSize = app(CohortFilterBuilder::class)
            ->build($filters)
            ->count();
    }

    public function saveCohort(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'gender' => ['nullable', 'string', 'in:male,female,other'],
        ]);

        if (! is_null($validated['min_age']) && ! is_null($validated['max_age']) && $validated['min_age'] > $validated['max_age']) {
            $this->addError('max_age', 'Max age must be greater than or equal to min age.');

            return;
        }

        $filters = $this->buildFilters();
        $estimatedSize = app(CohortFilterBuilder::class)->build($filters)->count();
        $this->estimatedSize = $estimatedSize;

        $cohortId = Str::uuid()->toString();

        try {
            app(KThresholdService::class)->enforce($estimatedSize);
        } catch (CohortSuppressedException $e) {
            $this->addError('name', $e->getMessage());

            return;
        }

        ResearcherCohort::create([
            'id' => $cohortId,
            'name' => $validated['name'],
            'purpose' => $validated['purpose'],
            'filters_json' => $filters,
            'estimated_size' => $estimatedSize,
            'version' => 1,
            'created_by' => Auth::user()->account_id,
        ]);

        AuditLogger::log(
            'researcher_cohort_created',
            ['reporting', 'researcher', 'outcome:success'],
            null,
            [],
            [
                'cohort_id' => $cohortId,
                'estimated_size' => $estimatedSize,
                'filters' => $filters,
                'version' => 1,
            ]
        );

        $this->reportMessage = 'Cohort saved successfully.';
        $this->reset(['name', 'purpose']);
        $this->loadSavedCohorts();
        $this->selectedCohortId = $cohortId;
    }

    public function deleteCohort(string $cohortId): void
    {
        $accountId = Auth::user()?->account_id;

        $cohort = ResearcherCohort::query()
            ->where('created_by', $accountId)
            ->findOrFail($cohortId);

        $cohort->delete();

        if ($this->selectedCohortId === $cohortId) {
            $this->selectedCohortId = null;
            $this->reset(['min_age', 'max_age', 'gender']);
        }

        $this->reportMessage = 'Cohort deleted.';
        $this->loadSavedCohorts();
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

    protected function loadSavedCohorts(): void
    {
        $accountId = Auth::user()?->account_id;

        $this->savedCohorts = ResearcherCohort::query()
            ->where('created_by', $accountId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (ResearcherCohort $cohort) => [
                'id' => $cohort->id,
                'name' => $cohort->name,
                'purpose' => $cohort->purpose,
                'estimated_size' => $cohort->estimated_size,
                'version' => $cohort->version,
                'created_at' => optional($cohort->created_at)?->toDateTimeString(),
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.researcher.cohort-report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Cohort Report Generator',
            ]);
    }
}

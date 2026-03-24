<?php

namespace App\Livewire\Researcher;

use Livewire\Component;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Services\AggregatedMetricsService;
use Illuminate\Support\Str;
use App\Exceptions\CohortSuppressedException;
use App\Models\ResearcherCohort;
use App\Models\AggregatedData;
use App\Models\Report;
use Carbon\CarbonImmutable;

class ResearcherReportGenerator extends Component
{
    public $name;

    public $purpose;

    public $cohorts;

    public $min_age;

    public $max_age;

    public $gender;

    public $from;

    public $to;

    public $selectedCohort;

    public $keys;

    protected $messages = [
        'min_age.min' => 'The minimum age must be at least 0 and at most 120',
        'max_age.min' => 'The maximum age must be at least 0 and at most 120',
        'max_age.gte' => 'The maximum age must be higher than (or equal to) the minimum age.',
        'from.required' => 'Start Date is required',
        'to.required' => 'End Date is required',
        'to.after_or_equal' => 'End date must be greater than or equal to Start Date',
    ];

    public function mount()
    {
        $this->name = "";

        $this->purpose = "";

        $this->cohorts = ResearcherCohort::all();
    }

    public function store(
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold
    ) {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],
            'min_age' => ['nullable', 'integer', 'min:0', 'max:120'],
            'max_age' => ['nullable', 'integer', 'min:0', 'max:120', 'gte:min_age'],
            'gender' => ['nullable', 'string', 'max:50'],
        ]);

        $filters = array_filter([
            'account_type' => 'User',
            'name'         => $this->name,
            'purpose'      => $this->purpose,
            'min_age'      => $this->min_age,
            'max_age'      => $this->max_age,
            'gender'       => $this->gender,
        ], fn($value) => !is_null($value) && $value !== '');

        try {
            $cohortQuery = $cohortBuilder->build($filters);

            $accountIds = $cohortQuery->pluck('id')->all();
            $cohortSize = count($accountIds);

            $threshold->enforce($cohortSize, 10);

            $cohortId = Str::uuid()->toString();

            ResearcherCohort::create([
                'id'             => $cohortId,
                'name'           => $this->name,
                'purpose'        => $this->purpose,
                'filters_json'   => $filters,
                'estimated_size' => $cohortSize,
                'version'        => 1,
                'created_by'     => auth->user()->account_id,
            ]);

            AuditLogger::log(
                'researcher_cohort_created',
                ['reporting', 'researcher', 'outcome:success'],
                null,
                [],
                [
                    'cohort_id' => $cohortId,
                    'cohort_size' => $cohortSize,
                    'filter_keys' => array_keys($filters),
                    'version' => 1,
                ]
            );

            session()->flash('success', 'Cohort created successfully');
        } catch (CohortSuppressedException $e) {
            AuditLogger::log(
                'researcher_cohort_rejected',
                ['reporting', 'researcher', 'outcome:blocked', 'reason:k_threshold'],
                null,
                [],
                [
                    'filter_keys' => array_keys($filters),
                ]
            );

            $this->addError('cohort', $e->getMessage());
        }
    }

    public function generateReport(AggregatedMetricsService $aggregator)
    {
        $validated = $this->validate([
            'selectedCohort' => ['required'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
            'keys' => ['sometimes', 'string'],
        ]);

        $keys = [];
        if (!empty($validated['keys'])) {
            $keys = array_values(array_filter(array_map('trim', explode(',', $validated['keys']))));
        }

        $accountIds = $this->selectedCohort->pluck('id')->all();
        $from = CarbonImmutable::parse($validated['from'])->startOfDay();
        $to = CarbonImmutable::parse($validated['to'])->endOfDay();

        $metrics = $aggregator->aggregateForCohort(
            $accountIds,
            $from,
            $to,
            $keys
        );

        $report = Report::create([
            'researcher_id' => auth->user()->account_id,
            'report_type' => 'Aggregated'
        ]);

        AggregatedData::create([
            'report_id' => $report->id,
            'metrics' => $metrics
        ]);

        session()->flash('success', 'Report created successfully');
    }

    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Report Generator'
            ]);
    }
}

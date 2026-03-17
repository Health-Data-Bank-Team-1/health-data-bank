<?php

namespace App\Livewire\Researcher;

use Livewire\Component;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Exceptions\CohortSuppressedException;

class ResearcherReportGenerator extends Component
{
    public $name;

    public $purpose;

    public $filters = [];

    public function mount()
    {
        $this->filters = [
            ['field' => '', 'operator' => '', 'value' => '']
        ];

        $this->name = "";

        $this->purpose = "";
    }

    public function addFilter()
    {
        $this->filters[] = ['field' => '', 'operator' => '', 'value' => ''];
    }

    public function removeFilter($index)
    {
        unset($this->filters[$index]);
        $this->filters = array_values($this->filters);
    }

    public function buildFilters()
    {
        $filters = [];

        foreach ($this->filters as $filter) {
            foreach ($this->filters as $filter) {
                if (!$filter['field'] || !$filter['value']) {
                    continue;
                }

                $filters[$filter['field']] = $filter['value'];
            }
        }

        return $filters;
    }

    public function generateCohort(CohortFilterBuilder $cohortBuilder, KThresholdService $threshold)
    {
        $this->store($cohortBuilder, $threshold);
    }

    public function store(
        CohortFilterBuilder $cohortBuilder,
        KThresholdService $threshold
    ) {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:500'],
        ]);

        $filters = $this->buildFilters();
        $filters['account_type'] = 'User';
        $filters['account_status'] = $validated['account_status'] ?? 'ACTIVE';

        $filters = array_filter($filters, fn($value) => $value !== null);

        try {
            $cohortQuery = $cohortBuilder->build($filters);

            $accountIds = $cohortQuery->pluck('id')->all();
            $cohortSize = count($accountIds);

            $threshold->enforce($cohortSize, 10);

            $user = auth->user();
            $cohortId = Str::uuid()->toString();

            DB::table('researcher_cohorts')->insert([
                'id' => $cohortId,
                'name' => $validated['name'],
                'purpose' => $validated['purpose'],
                'filters_json' => json_encode($filters),
                'estimated_size' => $cohortSize,
                'version' => 1,
                'created_by' => $user?->account_id ?? $user?->id,
                'created_at' => now(),
                'updated_at' => now(),
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

    public function render()
    {
        return view('livewire.researcher.report-generator')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Report Generator'
            ]);
    }
}

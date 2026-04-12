<?php

namespace App\Livewire\Researcher;

use App\Models\ResearcherCohort;
use App\Services\AuditLogger;
use App\Services\CohortFilterBuilder;
use App\Services\KThresholdService;
use App\Exceptions\CohortSuppressedException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class CohortBuilder extends Component
{
    public string $name = '';
    public string $purpose = '';
    public ?int $min_age = null;
    public ?int $max_age = null;
    public ?string $gender = null;

    public ?int $estimatedSize = null;

    public function estimateSize(): void
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

        if (!is_null($validated['min_age']) && !is_null($validated['max_age']) && $validated['min_age'] > $validated['max_age']) {
            $this->addError('max_age', 'Max age must be greater than or equal to min age.');
            return;
        }

        $filters = $this->buildFilters();

        $query = app(CohortFilterBuilder::class)->build($filters);
        $estimatedSize = $query->count();

        $this->estimatedSize = $estimatedSize;
        $cohortId = Str::uuid()->toString();

        try {
            app(KThresholdService::class)->enforce($estimatedSize);
        } catch (CohortSuppressedException $e) {
            $this->addError('name', $e->getMessage());
            return;
        }

        $cohort= ResearcherCohort::create([
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

        session()->flash('message', 'Cohort created successfully.');

        $this->reset(['name', 'purpose', 'min_age', 'max_age', 'gender']);
        $this->estimatedSize = null;
    }

    protected function buildFilters(): array
    {
        return array_filter([
            'account_type' => 'User',
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'gender' => $this->gender,
        ], fn ($value) => !is_null($value) && $value !== '');
    }

    public function render()
    {
        return view('livewire.researcher.cohort')
            ->layout('layouts.researcher')
            ->layoutData([
                'header' => 'Manage Cohorts',
            ]);
    }
}

<?php

namespace App\Livewire\Provider;

use App\Models\Account;
use App\Models\FormField;
use App\Services\AuditLogger;
use App\Services\ProviderAnalyticsServices;
use Livewire\Component;

class ProviderReports extends Component
{
    public string $mode = 'participants';
    public array $participant_ids = [];
    public array $metrics = [];
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $granularity = 'day';

    public array $group_a = [
        'location' => '',
        'age_min' => '',
        'age_max' => '',
        'gender' => [],
    ];

    public array $group_b = [
        'location' => '',
        'age_min' => '',
        'age_max' => '',
        'gender' => [],
    ];

    public $participants = [];
    public $availableMetrics = [];
    public $report = null;

    public function mount(): void
    {
        $this->participants = Account::query()
            ->where('account_type', 'User')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $this->availableMetrics = FormField::query()
            ->whereNotNull('metric_key')
            ->distinct()
            ->pluck('metric_key')
            ->filter()
            ->values()
            ->toArray();

        $this->date_from = now()->subMonth()->toDateString();
        $this->date_to = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'mode' => 'required|in:participants,group',
            'metrics' => 'required|array|min:1',
            'metrics.*' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'granularity' => 'required|in:day,week,month',
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'nullable|uuid|exists:accounts,id',
            'group_a.location' => 'nullable|string|max:255',
            'group_a.age_min' => 'nullable|integer|min:0|max:120',
            'group_a.age_max' => 'nullable|integer|min:0|max:120',
            'group_a.gender' => 'nullable|array',
            'group_b.location' => 'nullable|string|max:255',
            'group_b.age_min' => 'nullable|integer|min:0|max:120',
            'group_b.age_max' => 'nullable|integer|min:0|max:120',
            'group_b.gender' => 'nullable|array',
        ];
    }

    public function generateReport(ProviderAnalyticsServices $analyticsService, AuditLogger $auditLogger): void
    {
        $this->validate();

        if ($this->mode === 'participants' && count($this->participant_ids) === 0) {
            $this->addError('participant_ids', 'Please select at least one participant.');
            return;
        }

        if ($this->mode === 'participants') {
            $this->report = $analyticsService->generateParticipantReport(
                participantIds: $this->participant_ids,
                metrics: $this->metrics,
                dateFrom: $this->date_from,
                dateTo: $this->date_to,
                granularity: $this->granularity,
            );

            $auditLogger->log(
                event: 'provider_participant_report_viewed',
                tags: ['provider', 'reporting', 'participants'],
                auditable: auth()->user(),
                oldValues: [],
                newValues: [
                    'participant_ids' => $this->participant_ids,
                    'metrics' => $this->metrics,
                    'date_from' => $this->date_from,
                    'date_to' => $this->date_to,
                ]
            );
        } else {
            $this->report = $analyticsService->generateGroupComparisonReport(
                groupAFilters: $this->group_a,
                groupBFilters: $this->group_b,
                metrics: $this->metrics,
                dateFrom: $this->date_from,
                dateTo: $this->date_to,
                granularity: $this->granularity,
                minimumGroupSize: 10,
            );

            $auditLogger->log(
                event: 'provider_group_report_viewed',
                tags: ['provider', 'reporting', 'group'],
                auditable: auth()->user(),
                oldValues: [],
                newValues: [
                    'group_a' => $this->group_a,
                    'group_b' => $this->group_b,
                    'metrics' => $this->metrics,
                    'date_from' => $this->date_from,
                    'date_to' => $this->date_to,
                ]
            );
        }
    }

    public function render()
    {
        return view('livewire.provider.reports')
            ->layout('layouts.provider')
            ->layoutData([
                'header' => 'Reports'
            ]);
    }
}

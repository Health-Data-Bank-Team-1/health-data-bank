<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderAnalyticsRequest;
use App\Models\Account;
use App\Models\FormField;
use App\Services\AuditLogger;
use App\Services\ProviderAnalyticsServices;
use Illuminate\Http\Request;

class ProviderAnalyticsController extends Controller
{
    public function __construct(
        protected ProviderAnalyticsServices $analyticsService,
        protected AuditLogger $auditLogger
    ) {
    }

    public function index(Request $request)
    {
        $participants = Account::query()
            ->where('account_type', 'User')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $metrics = FormField::query()
            ->whereNotNull('metric_key')
            ->distinct()
            ->pluck('metric_key')
            ->filter()
            ->values();

        return view('livewire.provider.reports', [
            'participants' => $participants,
            'metrics' => $metrics,
            'report' => null,
        ]);
    }

    public function report(ProviderAnalyticsRequest $request)
    {
        $validated = $request->validated();

        if ($validated['mode'] === 'participants') {
            $report = $this->analyticsService->generateParticipantReport(
                participantIds: $validated['participant_ids'],
                metrics: $validated['metrics'],
                dateFrom: $validated['date_from'],
                dateTo: $validated['date_to'],
                granularity: $validated['granularity'] ?? 'day',
            );

            $this->auditLogger->log(
                event: 'provider_participant_report_viewed',
                tags: ['provider', 'reporting', 'participants'],
                auditable: auth()->user(),
                oldValues: [],
                newValues: [
                    'participant_ids' => $validated['participant_ids'],
                    'metrics' => $validated['metrics'],
                    'date_from' => $validated['date_from'],
                    'date_to' => $validated['date_to'],
                ]
            );
        } else {
            $report = $this->analyticsService->generateGroupComparisonReport(
                groupAFilters: $validated['group_a'] ?? [],
                groupBFilters: $validated['group_b'] ?? [],
                metrics: $validated['metrics'],
                dateFrom: $validated['date_from'],
                dateTo: $validated['date_to'],
                granularity: $validated['granularity'] ?? 'day',
                minimumGroupSize: 10,
            );

            $this->auditLogger->log(
                event: 'provider_group_report_viewed',
                tags: ['provider', 'reporting', 'group'],
                auditable: auth()->user(),
                oldValues: [],
                newValues: [
                    'group_a' => $validated['group_a'] ?? [],
                    'group_b' => $validated['group_b'] ?? [],
                    'metrics' => $validated['metrics'],
                    'date_from' => $validated['date_from'],
                    'date_to' => $validated['date_to'],
                ]
            );
        }

        $participants = Account::query()
            ->where('account_type', 'User')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $metrics = FormField::query()
            ->whereNotNull('metric_key')
            ->distinct()
            ->pluck('metric_key')
            ->filter()
            ->values();

        return view('livewire.provider.reports', [
            'participants' => $participants,
            'metrics' => $metrics,
            'report' => $report,
        ]);
    }
}

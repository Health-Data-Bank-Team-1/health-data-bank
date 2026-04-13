<?php

namespace App\Services;

use App\Models\HealthEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PersonalComparisonService
{
    public function __construct(
        private CohortFilterBuilder $cohortFilterBuilder,
        private AggregatedMetricsService $aggregatedMetricsService,
        private KThresholdService $kThresholdService
    ) {}

    public function compare(
        string $metricKey,
        string $from,
        string $to,
        array $filters = []
    ): array {

        $user = Auth::user();

        $accountId = $user->account_id;

        $fromDate = Carbon::parse($from)->startOfDay();
        $toDate = Carbon::parse($to)->endOfDay();

        /**
         * Step 1 — Get user's metric values
         */
        $userEntries = HealthEntry::query()
            ->where('account_id', $accountId)
            ->whereBetween('timestamp', [$fromDate, $toDate])
            ->whereHas('submission', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->get(['encrypted_values']);

        $userValues = [];

        foreach ($userEntries as $entry) {

            $values = $entry->encrypted_values ?? [];

            if (!is_array($values)) {
                continue;
            }

            if (!array_key_exists($metricKey, $values)) {
                continue;
            }

            $value = $values[$metricKey];

            if (is_numeric($value)) {
                $userValues[] = (float) $value;
            }
        }

        $userValue = count($userValues) > 0
            ? array_sum($userValues) / count($userValues)
            : null;

        /**
         * Step 2 — Build cohort
         */
        $cohortAccounts = $this->cohortFilterBuilder
            ->build($filters)
            ->pluck('accounts.id')
            ->toArray();

        /**
         * Step 3 — Aggregate cohort metrics
         */
        $aggregates = $this->aggregatedMetricsService
            ->aggregateForCohort(
                $cohortAccounts,
                $fromDate,
                $toDate,
                [$metricKey]
            );

        $group = $aggregates[$metricKey] ?? [
            'count' => 0,
            'avg' => null
        ];

        /**
         * Step 4 — Enforce suppression rule
         */
        $this->kThresholdService->enforce($group['count']);

        /**
         * Step 5 — Return comparison payload
         */
        return [
            'metric_key' => $metricKey,
            'date_from' => $fromDate->toDateString(),
            'date_to' => $toDate->toDateString(),
            'user_value' => $userValue,
            'group' => [
                'is_suppressed' => false,
                'count' => $group['count'],
                'avg' => $group['avg'],
            ]
        ];
    }
}

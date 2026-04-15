<?php

namespace App\Services;

use App\Models\Account;
use App\Models\HealthEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProviderAnalyticsServices
{
    public function generateParticipantReport(array $participantIds, array $metrics, string $dateFrom, string $dateTo, string $granularity = 'day'): array
    {
        $accounts = Account::query()
            ->whereIn('id', $participantIds)
            ->where('account_type', 'User')
            ->get(['id', 'name', 'email']);

        $entries = HealthEntry::query()
            ->whereIn('account_id', $accounts->pluck('id'))
            ->whereBetween('timestamp', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->orderBy('timestamp')
            ->get();

        $charts = [];
        $summary = [];

        foreach ($metrics as $metric) {
            $series = [];
            $metricValues = [];

            foreach ($accounts as $account) {
                $accountEntries = $entries->where('account_id', $account->id);
                $bucketed = $this->buildTimeSeries($accountEntries, $metric, $granularity);

                $series[] = [
                    'label' => $account->name,
                    'data' => array_values($bucketed['values']),
                ];

                $metricValues = array_merge($metricValues, $this->extractMetricValues($accountEntries, $metric));
            }

            $labels = $this->buildAllLabels($entries, $granularity);

            $charts[] = [
                'metric' => $metric,
                'labels' => $labels,
                'datasets' => $this->alignSeriesToLabels($series, $entries, $metric, $granularity, $accounts),
            ];

            $summary[] = [
                'metric' => $metric,
                'count' => count($metricValues),
                'average' => count($metricValues) ? round(array_sum($metricValues) / count($metricValues), 2) : null,
                'min' => count($metricValues) ? min($metricValues) : null,
                'max' => count($metricValues) ? max($metricValues) : null,
            ];
        }

        return [
            'type' => 'participants',
            'participants' => $accounts,
            'charts' => $charts,
            'summary' => $summary,
        ];
    }

    public function generateGroupComparisonReport(array $groupAFilters, ?array $groupBFilters, array $metrics, string $dateFrom, string $dateTo, string $granularity = 'day', int $minimumGroupSize = 10): array
    {
        $groupAAccounts = $this->applyGroupFilters(Account::query()->where('account_type', 'User'), $groupAFilters)->get();
        $groupBAccounts = collect();

        if (!empty($groupBFilters)) {
            $groupBAccounts = $this->applyGroupFilters(Account::query()->where('account_type', 'User'), $groupBFilters)->get();
        }

        if ($groupAAccounts->count() < $minimumGroupSize) {
            abort(422, 'Group A is too small. Please broaden the filters.');
        }

        if ($groupBFilters && $groupBAccounts->count() < $minimumGroupSize) {
            abort(422, 'Group B is too small. Please broaden the filters.');
        }

        $groupAEntries = HealthEntry::query()
            ->whereIn('account_id', $groupAAccounts->pluck('id'))
            ->whereBetween('timestamp', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->orderBy('timestamp')
            ->get();

        $groupBEntries = collect();
        if ($groupBAccounts->isNotEmpty()) {
            $groupBEntries = HealthEntry::query()
                ->whereIn('account_id', $groupBAccounts->pluck('id'))
                ->whereBetween('timestamp', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay(),
                ])
                ->orderBy('timestamp')
                ->get();
        }

        $charts = [];
        $summary = [];

        foreach ($metrics as $metric) {
            $groupASeries = $this->buildTimeSeries($groupAEntries, $metric, $granularity);
            $labels = array_keys($groupASeries['values']);

            $datasets = [
                [
                    'label' => 'Group A',
                    'data' => array_values($groupASeries['values']),
                ]
            ];

            $groupAValues = $this->extractMetricValues($groupAEntries, $metric);

            $summaryRow = [
                'metric' => $metric,
                'group_a_count' => count($groupAValues),
                'group_a_average' => count($groupAValues) ? round(array_sum($groupAValues) / count($groupAValues), 2) : null,
            ];

            if ($groupBAccounts->isNotEmpty()) {
                $groupBSeries = $this->buildTimeSeries($groupBEntries, $metric, $granularity);

                $allLabels = array_values(array_unique(array_merge($labels, array_keys($groupBSeries['values']))));
                sort($allLabels);

                $datasets = [
                    [
                        'label' => 'Group A',
                        'data' => $this->mapValuesToLabels($groupASeries['values'], $allLabels),
                    ],
                    [
                        'label' => 'Group B',
                        'data' => $this->mapValuesToLabels($groupBSeries['values'], $allLabels),
                    ],
                ];

                $labels = $allLabels;

                $groupBValues = $this->extractMetricValues($groupBEntries, $metric);

                $summaryRow['group_b_count'] = count($groupBValues);
                $summaryRow['group_b_average'] = count($groupBValues) ? round(array_sum($groupBValues) / count($groupBValues), 2) : null;
            }

            $charts[] = [
                'metric' => $metric,
                'labels' => $labels,
                'datasets' => $datasets,
            ];

            $summary[] = $summaryRow;
        }

        return [
            'type' => 'group',
            'group_a_size' => $groupAAccounts->count(),
            'group_b_size' => $groupBAccounts->count(),
            'charts' => $charts,
            'summary' => $summary,
        ];
    }

    protected function applyGroupFilters(Builder $query, array $filters): Builder
    {
        if (!empty($filters['gender'])) {
            $query->whereIn('gender', $filters['gender']);
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['age_min'])) {
            $maxDob = now()->subYears((int) $filters['age_min'])->endOfDay();
            $query->whereDate('date_of_birth', '<=', $maxDob);
        }

        if (!empty($filters['age_max'])) {
            $minDob = now()->subYears((int) $filters['age_max'] + 1)->addDay()->startOfDay();
            $query->whereDate('date_of_birth', '>=', $minDob);
        }

        return $query;
    }

    protected function buildTimeSeries(Collection $entries, string $metric, string $granularity = 'day'): array
    {
        $buckets = [];

        foreach ($entries as $entry) {
            $value = $this->extractMetricValue($entry, $metric);

            if ($value === null || !is_numeric($value)) {
                continue;
            }

            $label = $this->formatBucket($entry->timestamp, $granularity);

            if (!isset($buckets[$label])) {
                $buckets[$label] = [];
            }

            $buckets[$label][] = (float) $value;
        }

        ksort($buckets);

        $averages = [];
        foreach ($buckets as $label => $values) {
            $averages[$label] = round(array_sum($values) / count($values), 2);
        }

        return [
            'values' => $averages,
        ];
    }

    protected function extractMetricValue(HealthEntry $entry, string $metric): mixed
    {
        $payload = $entry->encrypted_values ?? [];

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            $payload = is_array($decoded) ? $decoded : [];
        }

        return data_get($payload, $metric);
    }

    protected function extractMetricValues(Collection $entries, string $metric): array
    {
        return $entries
            ->map(fn ($entry) => $this->extractMetricValue($entry, $metric))
            ->filter(fn ($value) => $value !== null && is_numeric($value))
            ->map(fn ($value) => (float) $value)
            ->values()
            ->all();
    }

    protected function formatBucket(string|\DateTimeInterface $timestamp, string $granularity): string
    {
        $date = Carbon::parse($timestamp);

        return match ($granularity) {
            'month' => $date->format('Y-m'),
            'week' => $date->startOfWeek()->format('Y-m-d'),
            default => $date->format('Y-m-d'),
        };
    }

    protected function buildAllLabels(Collection $entries, string $granularity): array
    {
        return $entries
            ->map(fn ($entry) => $this->formatBucket($entry->timestamp, $granularity))
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    protected function mapValuesToLabels(array $valuesByLabel, array $labels): array
    {
        return collect($labels)
            ->map(fn ($label) => $valuesByLabel[$label] ?? null)
            ->values()
            ->all();
    }

    protected function alignSeriesToLabels(array $series, Collection $entries, string $metric, string $granularity, Collection $accounts): array
    {
        $labels = $this->buildAllLabels($entries, $granularity);
        $datasets = [];

        foreach ($accounts as $index => $account) {
            $accountEntries = $entries->where('account_id', $account->id);
            $seriesValues = $this->buildTimeSeries($accountEntries, $metric, $granularity)['values'];

            $datasets[] = [
                'label' => $account->name,
                'data' => $this->mapValuesToLabels($seriesValues, $labels),
            ];
        }

        return $datasets;
    }
}

<?php

namespace App\Services;

use App\Models\HealthEntry;
use Carbon\CarbonInterface;

class ReportingAggregationService
{
    /**
     * Aggregate metrics for a single account across a time range.
     *
     * Notes:
     * - "count" reflects numeric datapoints only (values that are int/float/numeric-string).
     * - "latest" is the latest raw value seen for the key (may be non-numeric).
     *
     * @return array<string, array{count:int, min:float|null, max:float|null, avg:float|null, latest:mixed, latest_at:string|null}>
     */
    public function aggregateForAccount(
        string $accountId,
        CarbonInterface $from,
        CarbonInterface $to,
        array $onlyKeys = []
    ): array {
        $entries = HealthEntry::query()
            ->where('account_id', $accountId)
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp')
            ->get(['timestamp', 'encrypted_values']);

        //metricKey => list of ['ts' => mixed, 'value' => mixed]
        $series = [];

        foreach ($entries as $entry) {
            $values = $entry->encrypted_values ?? [];

            //if encrypted_values isn't an array, skip
            if (!is_array($values)) {
                continue;
            }

            foreach ($values as $key => $value) {
                if ($onlyKeys && !in_array($key, $onlyKeys, true)) {
                    continue;
                }

                $series[$key][] = [
                    'ts' => $entry->timestamp,
                    'value' => $value,
                ];
            }
        }

        $out = [];

        foreach ($series as $key => $points) {
            $values = array_map(static fn (array $p) => $p['value'], $points);

            //numeric-only aggregates
            $numeric = array_values(array_filter(
                $values,
                static fn ($v) =>
                    is_int($v) ||
                    is_float($v) ||
                    (is_string($v) && is_numeric($v))
            ));
            $numeric = array_map('floatval', $numeric);

            $latestPoint = $points[count($points) - 1] ?? null;

            $latestAt = $latestPoint['ts'] ?? null;
            $latestAtIso = $latestAt
                ? ($latestAt instanceof \Carbon\CarbonInterface ? $latestAt->toIso8601String() : (string) $latestAt)
                : null;

            $out[$key] = [
                'count' => count($numeric),
                'min' => $numeric ? min($numeric) : null,
                'max' => $numeric ? max($numeric) : null,
                'avg' => $numeric ? (array_sum($numeric) / count($numeric)) : null,
                'latest' => $latestPoint['value'] ?? null,
                'latest_at' => $latestAtIso,
            ];
        }

        return $out;
    }
}

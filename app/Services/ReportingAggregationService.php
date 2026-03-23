<?php

namespace App\Services;

use App\Models\HealthEntry;
use Carbon\CarbonInterface;

class ReportingAggregationService
{
    /**
     * Aggregate metrics for a single account across a time range.
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

        //metricKey => list of [ts, value]
        $series = [];

        foreach ($entries as $entry) {
            $values = $entry->encrypted_values ?? [];

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
            $out[$key] = $this->aggregatePointSeries($points);
        }

        return $out;
    }

    /**
     * Aggregate a series of timestamped values.
     *
     * @param array<int, array{ts:mixed, value:mixed}> $points
     * @return array{count:int, min:float|null, max:float|null, avg:float|null, latest:mixed, latest_at:string|null}
     */
    public function aggregatePointSeries(array $points): array
    {
        $values = array_map(fn ($p) => $p['value'], $points);

        $numeric = array_values(array_filter(
            $values,
            fn ($v) => is_int($v) || is_float($v) || (is_string($v) && is_numeric($v))
        ));
        $numeric = array_map('floatval', $numeric);

        $latestPoint = end($points) ?: null;

        return [
            'count' => count($numeric),
            'min' => $numeric ? min($numeric) : null,
            'max' => $numeric ? max($numeric) : null,
            'avg' => $numeric ? (array_sum($numeric) / count($numeric)) : null,
            'latest' => $latestPoint['value'] ?? null,
            'latest_at' => isset($latestPoint['ts']) && $latestPoint['ts']
                ? $latestPoint['ts']->toIso8601String()
                : null,
        ];
    }
}

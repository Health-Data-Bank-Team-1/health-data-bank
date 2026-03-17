<?php

namespace App\Services;

use App\Models\HealthEntry;
use Carbon\CarbonInterface;

class AggregatedMetricsService
{
    /**
     * @param array<int, string> $accountIds
     * @param array<int, string> $onlyKeys
     * @return array<string, array{count:int, avg:float|null}>
     */
    public function aggregateForCohort(
        array $accountIds,
        CarbonInterface $from,
        CarbonInterface $to,
        array $onlyKeys = []
    ): array {
        $entries = HealthEntry::query()
            ->whereIn('account_id', $accountIds)
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp')
            ->get(['encrypted_values']);

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

                if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
                    $series[$key][] = (float) $value;
                }
            }
        }

        $out = [];

        foreach ($series as $key => $values) {
            $out[$key] = [
                'count' => count($values),
                'avg' => count($values) > 0 ? array_sum($values) / count($values) : null,
            ];
        }

        return $out;
    }
}

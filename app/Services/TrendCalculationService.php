<?php

namespace App\Services;

use App\Models\HealthEntry;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class TrendCalculationService
{
    public function __construct(
        private readonly ReportingAggregationService $aggregation
    ) {
    }

    /**
     * Build a bucketed time-series for one metric key.
     *
     * @return array{
     *   metric:string,
     *   bucket:string,
     *   from:string,
     *   to:string,
     *   points: array<int, array{
     *     bucket_start:string,
     *     count:int,
     *     min:float|null,
     *     max:float|null,
     *     avg:float|null,
     *     latest:mixed,
     *     latest_at:string|null
     *   }>
     * }
     */
    public function trendForAccount(
        string $accountId,
        string $metricKey,
        CarbonInterface $from,
        CarbonInterface $to,
        string $bucket = 'day'
    ): array {
        $bucket = strtolower($bucket);

        $entries = HealthEntry::query()
            ->where('account_id', $accountId)
            ->whereBetween('timestamp', [$from, $to])
            ->orderBy('timestamp')
            ->get(['timestamp', 'encrypted_values']);

        // bucketStartIso => array of points [ts => CarbonImmutable, value => mixed]
        $buckets = [];

        foreach ($entries as $entry) {
            $values = $entry->encrypted_values;

            if (!is_array($values)) {
                continue;
            }

            if (!array_key_exists($metricKey, $values)) {
                continue;
            }

            $ts = $entry->timestamp instanceof \DateTimeInterface
                ? CarbonImmutable::instance($entry->timestamp)
                : CarbonImmutable::parse((string) $entry->timestamp);

            $bucketStart = $this->bucketStart($ts, $bucket);
            $bucketKey = $bucketStart->toIso8601String();

            $buckets[$bucketKey][] = [
                'ts' => $ts,
                'value' => $values[$metricKey],
            ];
        }

        ksort($buckets);

        $points = [];

        foreach ($buckets as $bucketStartIso => $bucketPoints) {
            usort($bucketPoints, fn ($a, $b) => $a['ts'] <=> $b['ts']);

            $stats = $this->aggregation->aggregateMetricPointSeries($metricKey, $bucketPoints);

            $points[] = [
                'bucket_start' => $bucketStartIso,
                ...$stats,
            ];
        }

        return [
            'metric' => $metricKey,
            'bucket' => $bucket,
            'from' => CarbonImmutable::instance($from)->toIso8601String(),
            'to' => CarbonImmutable::instance($to)->toIso8601String(),
            'points' => $points,
        ];
    }

    private function bucketStart(CarbonImmutable $ts, string $bucket): CarbonImmutable
    {
        return match ($bucket) {
            'day' => $ts->startOfDay(),
            'week' => $ts->startOfWeek(),
            'month' => $ts->startOfMonth(),
            default => $ts->startOfDay(),
        };
    }
}

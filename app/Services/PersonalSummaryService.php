<?php

namespace App\Services;

use Carbon\CarbonInterface;

class PersonalSummaryService
{
    public function __construct(
        private readonly ReportingAggregationService $aggregation
    ) {}

    /**
     * Return averages for each numeric metric key in a range.
     *
     * @return array{
     *   from:string,
     *   to:string,
     *   averages: array<string, float|null>,
     *   counts: array<string, int>
     * }
     */
    public function summaryForAccount(
        string $accountId,
        CarbonInterface $from,
        CarbonInterface $to,
        array $onlyKeys = []
    ): array {
        $agg = $this->aggregation->aggregateForAccount($accountId, $from, $to, $onlyKeys);

        $averages = [];
        $counts = [];

        foreach ($agg as $key => $row) {
            //avg is numeric-only
            $averages[$key] = $row['avg'];
            $counts[$key] = $row['count'];
        }

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'averages' => $averages,
            'counts' => $counts,
        ];
    }
}

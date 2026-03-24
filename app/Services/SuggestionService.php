<?php

namespace App\Services;

use Carbon\CarbonInterface;

class SuggestionService
{
    public function __construct(
        private readonly ReportingAggregationService $aggregation,
        private readonly TrendCalculationService $trend
    ) {
    }

    /**
     * Generate suggestions for an account over a date range.
     *
     * @return array{
     *   from:string,
     *   to:string,
     *   suggestions: array<int, array{
     *     type:string,
     *     metric:string|null,
     *     severity:string,
     *     title:string,
     *     message:string,
     *     context:array<string, mixed>
     *   }>
     * }
     */
    public function generateForAccount(
        string $accountId,
        CarbonInterface $from,
        CarbonInterface $to,
        array $onlyKeys = []
    ): array {
        $agg = $this->aggregation->aggregateForAccount($accountId, $from, $to, $onlyKeys);

        if (empty($agg)) {
            return [
                'from' => $from->toIso8601String(),
                'to' => $to->toIso8601String(),
                'suggestions' => [
                    $this->buildNoDataSuggestion(),
                ],
            ];
        }

        $suggestions = [];
        $thresholds = $this->baseThresholds();
        $margins = $this->trendMargins();

        foreach ($agg as $metric => $row) {
            $count = $row['count'] ?? 0;
            $avg = $row['avg'] ?? null;

            if ($count < 3) {
                $suggestions[] = $this->buildInsufficientDataSuggestion($metric, $count);
            }

            if (
                isset($thresholds[$metric]) &&
                is_numeric($avg) &&
                (float) $avg > $thresholds[$metric]
            ) {
                $suggestions[] = $this->buildHighValueSuggestion(
                    $metric,
                    (float) $avg,
                    $thresholds[$metric]
                );
            }

            if (isset($margins[$metric])) {
                $trendAnalysis = $this->detectTrendDirection(
                    $accountId,
                    $metric,
                    $from,
                    $to,
                    $margins[$metric]
                );

                if ($trendAnalysis['direction'] === 'up') {
                    $suggestions[] = $this->buildNegativeTrendSuggestion(
                        $metric,
                        $trendAnalysis['first_avg'],
                        $trendAnalysis['last_avg'],
                        $trendAnalysis['margin']
                    );
                }

                if ($trendAnalysis['direction'] === 'down') {
                    $suggestions[] = $this->buildPositiveTrendSuggestion(
                        $metric,
                        $trendAnalysis['first_avg'],
                        $trendAnalysis['last_avg'],
                        $trendAnalysis['margin']
                    );
                }
            }
        }

        $suggestions = collect($suggestions)
            ->unique(fn ($suggestion) => $suggestion['type'] . '|' . ($suggestion['metric'] ?? 'global'))
            ->sortByDesc(fn ($suggestion) => $this->severityRank($suggestion['severity']))
            ->values()
            ->all();

        return [
            'from' => $from->toIso8601String(),
            'to' => $to->toIso8601String(),
            'suggestions' => $suggestions,
        ];
    }

    /**
     * @return array{
     *   direction:'up'|'down'|null,
     *   first_avg:float|null,
     *   last_avg:float|null,
     *   margin:float
     * }
     */
    private function detectTrendDirection(
        string $accountId,
        string $metric,
        CarbonInterface $from,
        CarbonInterface $to,
        float $margin
    ): array {
        $trend = $this->trend->trendForAccount($accountId, $metric, $from, $to, 'day');
        $points = $trend['points'] ?? [];

        if (count($points) < 2) {
            return [
                'direction' => null,
                'first_avg' => null,
                'last_avg' => null,
                'margin' => $margin,
            ];
        }

        $first = $points[0]['avg'] ?? null;
        $last = $points[count($points) - 1]['avg'] ?? null;

        if (!is_numeric($first) || !is_numeric($last)) {
            return [
                'direction' => null,
                'first_avg' => is_numeric($first) ? (float) $first : null,
                'last_avg' => is_numeric($last) ? (float) $last : null,
                'margin' => $margin,
            ];
        }

        $firstFloat = (float) $first;
        $lastFloat = (float) $last;

        if ($lastFloat > ($firstFloat + $margin)) {
            return [
                'direction' => 'up',
                'first_avg' => $firstFloat,
                'last_avg' => $lastFloat,
                'margin' => $margin,
            ];
        }

        if ($lastFloat < ($firstFloat - $margin)) {
            return [
                'direction' => 'down',
                'first_avg' => $firstFloat,
                'last_avg' => $lastFloat,
                'margin' => $margin,
            ];
        }

        return [
            'direction' => null,
            'first_avg' => $firstFloat,
            'last_avg' => $lastFloat,
            'margin' => $margin,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function baseThresholds(): array
    {
        return [
            'hr' => 85.0,
            'weight' => 200.0,
        ];
    }

    /**
     * @return array<string, float>
     */
    private function trendMargins(): array
    {
        return [
            'hr' => 5.0,
            'weight' => 3.0,
        ];
    }

    private function severityRank(string $severity): int
    {
        return match ($severity) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }

    /**
     * @return array{
     *   type:string,
     *   metric:null,
     *   severity:string,
     *   title:string,
     *   message:string,
     *   context:array<string, mixed>
     * }
     */
    private function buildNoDataSuggestion(): array
    {
        return [
            'type' => 'no_data',
            'metric' => null,
            'severity' => 'low',
            'title' => 'No data available',
            'message' => 'Not enough data is available to generate insights.',
            'context' => [],
        ];
    }

    /**
     * @return array{
     *   type:string,
     *   metric:string,
     *   severity:string,
     *   title:string,
     *   message:string,
     *   context:array<string, mixed>
     * }
     */
    private function buildInsufficientDataSuggestion(string $metric, int $count): array
    {
        return [
            'type' => 'insufficient_data',
            'metric' => $metric,
            'severity' => 'low',
            'title' => 'More data needed',
            'message' => 'More data is needed for reliable insights for this metric.',
            'context' => [
                'count' => $count,
            ],
        ];
    }

    /**
     * @return array{
     *   type:string,
     *   metric:string,
     *   severity:string,
     *   title:string,
     *   message:string,
     *   context:array<string, mixed>
     * }
     */
    private function buildHighValueSuggestion(string $metric, float $avg, float $threshold): array
    {
        return [
            'type' => 'high_value',
            'metric' => $metric,
            'severity' => 'medium',
            'title' => 'Metric is above expected range',
            'message' => 'Average value is above the expected range.',
            'context' => [
                'avg' => $avg,
                'threshold' => $threshold,
            ],
        ];
    }

    /**
     * @return array{
     *   type:string,
     *   metric:string,
     *   severity:string,
     *   title:string,
     *   message:string,
     *   context:array<string, mixed>
     * }
     */
    private function buildNegativeTrendSuggestion(
        string $metric,
        ?float $firstAvg,
        ?float $lastAvg,
        float $margin
    ): array {
        return [
            'type' => 'negative_trend',
            'metric' => $metric,
            'severity' => 'medium',
            'title' => 'Metric trend is worsening',
            'message' => 'Recent trend data suggests this metric may be moving in an unhealthy direction.',
            'context' => [
                'first_avg' => $firstAvg,
                'last_avg' => $lastAvg,
                'margin' => $margin,
            ],
        ];
    }

    /**
     * @return array{
     *   type:string,
     *   metric:string,
     *   severity:string,
     *   title:string,
     *   message:string,
     *   context:array<string, mixed>
     * }
     */
    private function buildPositiveTrendSuggestion(
        string $metric,
        ?float $firstAvg,
        ?float $lastAvg,
        float $margin
    ): array {
        return [
            'type' => 'positive_trend',
            'metric' => $metric,
            'severity' => 'low',
            'title' => 'Metric trend is improving',
            'message' => 'Recent trend data suggests this metric may be improving.',
            'context' => [
                'first_avg' => $firstAvg,
                'last_avg' => $lastAvg,
                'margin' => $margin,
            ],
        ];
    }
}

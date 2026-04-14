<?php

namespace App\Services;

use App\Models\HealthEntry;
use App\Models\HealthGoal;
use Carbon\Carbon;

class GoalProgressService
{
    public function calculate(HealthGoal $goal): array
    {
        $startDate = Carbon::parse($goal->start_date)->startOfDay();
        $endDate = $goal->end_date
            ? Carbon::parse($goal->end_date)->endOfDay()
            : now()->endOfDay();

        $entries = HealthEntry::query()
            ->where('account_id', $goal->account_id)
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->whereHas('submission', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->orderBy('timestamp')
            ->get(['encrypted_values']);

        $values = [];

        foreach ($entries as $entry) {
            $payload = $entry->encrypted_values ?? [];

            if (!is_array($payload)) {
                continue;
            }

            if (!array_key_exists($goal->metric_key, $payload)) {
                continue;
            }

            $rawValue = $payload[$goal->metric_key];

            if (is_int($rawValue) || is_float($rawValue) || (is_string($rawValue) && is_numeric($rawValue))) {
                $values[] = (float) $rawValue;
            }
        }

        $currentValue = count($values) > 0 ? array_sum($values) : 0.0;
        $hasData = count($values) > 0;

        $isMet = $hasData && match ($goal->comparison_operator) {
                '<=' => $currentValue <= $goal->target_value,
                '>=' => $currentValue >= $goal->target_value,
                '='  => (float) $currentValue === (float) $goal->target_value,
            };

        return [
            'metric_key' => $goal->metric_key,
            'timeframe' => $goal->timeframe,
            'comparison_operator' => $goal->comparison_operator,
            'target_value' => (float) $goal->target_value,
            'current_value' => $currentValue,
            'progress_percent' => $hasData ? $this->progressPercent($goal, $currentValue) : 0,
            'is_met' => $isMet,
            'entry_count' => count($values),
            'evaluated_from' => $startDate->toDateString(),
            'evaluated_to' => $endDate->toDateString(),
        ];
    }

    private function progressPercent(HealthGoal $goal, float $currentValue): float
    {
        if ((float) $goal->target_value <= 0) {
            return 0;
        }

        return match ($goal->comparison_operator) {
            '>=' => min(100, round(($currentValue / $goal->target_value) * 100, 2)),
            '<=' => min(100, round(($currentValue / $goal->target_value) * 100, 2)),
            '='  => max(0, round(
                100 - ((abs($currentValue - $goal->target_value) / $goal->target_value) * 100),
                2
            )),
        };
    }
}

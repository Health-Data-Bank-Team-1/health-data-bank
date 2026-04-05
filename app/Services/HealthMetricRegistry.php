<?php

namespace App\Services;

class HealthMetricRegistry
{
    /**
     * @return array<string, array{
     *   label:string,
     *   type:string,
     *   unit:string|null,
     *   threshold:float|null,
     *   trend_margin:float|null,
     *   threshold_enabled:bool,
     *   trend_enabled:bool
     * }>
     */
    public function all(): array
    {
        return [
            'hr' => [
                'label' => 'Heart Rate',
                'type' => 'number',
                'unit' => 'bpm',
                'threshold' => 85.0,
                'trend_margin' => 5.0,
                'threshold_enabled' => true,
                'trend_enabled' => true,
            ],
            'weight' => [
                'label' => 'Weight',
                'type' => 'number',
                'unit' => 'lb',
                'threshold' => 200.0,
                'trend_margin' => 3.0,
                'threshold_enabled' => true,
                'trend_enabled' => true,
            ],
            'mood' => [
                'label' => 'Mood',
                'type' => 'string',
                'unit' => null,
                'threshold' => null,
                'trend_margin' => null,
                'threshold_enabled' => false,
                'trend_enabled' => false,
            ],
        ];
    }

    /**
     * @return array{
     *   label:string,
     *   type:string,
     *   unit:string|null,
     *   threshold:float|null,
     *   trend_margin:float|null,
     *   threshold_enabled:bool,
     *   trend_enabled:bool
     * }|null
     */
    public function forMetric(string $metric): ?array
    {
        return $this->all()[$metric] ?? null;
    }

    /**
     * @return array<int, string>
     */
    public function supportedMetrics(): array
    {
        return array_keys($this->all());
    }

    public function hasMetric(string $metric): bool
    {
        return array_key_exists($metric, $this->all());
    }

    public function isNumeric(string $metric): bool
    {
        return ($this->forMetric($metric)['type'] ?? null) === 'number';
    }

    public function labelFor(string $metric): ?string
    {
        return $this->forMetric($metric)['label'] ?? null;
    }

    public function unitFor(string $metric): ?string
    {
        return $this->forMetric($metric)['unit'] ?? null;
    }

    public function thresholdFor(string $metric): ?float
    {
        $value = $this->forMetric($metric)['threshold'] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    public function trendMarginFor(string $metric): ?float
    {
        $value = $this->forMetric($metric)['trend_margin'] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    public function thresholdEnabled(string $metric): bool
    {
        return (bool) ($this->forMetric($metric)['threshold_enabled'] ?? false);
    }

    public function trendEnabled(string $metric): bool
    {
        return (bool) ($this->forMetric($metric)['trend_enabled'] ?? false);
    }
}

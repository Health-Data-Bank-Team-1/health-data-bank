<?php

namespace Tests\Feature\Suggestions;

use App\Services\HealthMetricRegistry;
use Tests\TestCase;

class HealthMetricRegistryTest extends TestCase
{
    public function test_it_returns_supported_metrics(): void
    {
        $registry = app(HealthMetricRegistry::class);

        $metrics = $registry->supportedMetrics();

        $this->assertContains('hr', $metrics);
        $this->assertContains('weight', $metrics);
        $this->assertContains('mood', $metrics);
    }

    public function test_it_knows_numeric_vs_non_numeric_metrics(): void
    {
        $registry = app(HealthMetricRegistry::class);

        $this->assertTrue($registry->isNumeric('hr'));
        $this->assertTrue($registry->isNumeric('weight'));
        $this->assertFalse($registry->isNumeric('mood'));
    }

    public function test_it_returns_threshold_and_margin_values(): void
    {
        $registry = app(HealthMetricRegistry::class);

        $this->assertSame(85.0, $registry->thresholdFor('hr'));
        $this->assertSame(5.0, $registry->trendMarginFor('hr'));

        $this->assertSame(200.0, $registry->thresholdFor('weight'));
        $this->assertSame(3.0, $registry->trendMarginFor('weight'));
    }

    public function test_it_disables_thresholds_and_trends_for_non_numeric_metrics(): void
    {
        $registry = app(HealthMetricRegistry::class);

        $this->assertFalse($registry->thresholdEnabled('mood'));
        $this->assertFalse($registry->trendEnabled('mood'));
        $this->assertNull($registry->thresholdFor('mood'));
        $this->assertNull($registry->trendMarginFor('mood'));
    }

    public function test_it_returns_null_for_unknown_metric(): void
    {
        $registry = app(HealthMetricRegistry::class);

        $this->assertNull($registry->forMetric('unknown_metric'));
        $this->assertFalse($registry->hasMetric('unknown_metric'));
    }
}

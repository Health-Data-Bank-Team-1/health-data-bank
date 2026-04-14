<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\TrendCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeSeriesForCohortTest extends TestCase
{
    use RefreshDatabase;

    private TrendCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TrendCalculationService::class);
    }

    public function test_it_returns_daily_buckets_across_multiple_accounts(): void
    {
        $account1 = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);
        $account2 = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account1->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account2->id,
            'timestamp' => '2026-02-01 14:00:00',
            'encrypted_values' => ['hr' => 90],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account1->id,
            'timestamp' => '2026-02-02 09:00:00',
            'encrypted_values' => ['hr' => 80],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account1->id, $account2->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-02 23:59:59'),
            'day'
        );

        $this->assertEquals('hr', $result['metric']);
        $this->assertEquals('day', $result['bucket']);
        $this->assertCount(2, $result['points']);

        $p1 = $result['points'][0];
        $this->assertEquals(2, $p1['count']);
        $this->assertEquals(70.0, $p1['min']);
        $this->assertEquals(90.0, $p1['max']);
        $this->assertEquals(80.0, $p1['avg']);

        $p2 = $result['points'][1];
        $this->assertEquals(1, $p2['count']);
        $this->assertEquals(80.0, $p2['avg']);
    }

    public function test_it_returns_empty_points_when_no_data(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-02'),
            'day'
        );

        $this->assertEquals('hr', $result['metric']);
        $this->assertEquals('day', $result['bucket']);
        $this->assertCount(0, $result['points']);
    }

    public function test_it_ignores_non_numeric_values(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => '72'],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 11:00:00',
            'encrypted_values' => ['hr' => 'note'],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-01 23:59:59'),
            'day'
        );

        $p1 = $result['points'][0];
        $this->assertEquals(1, $p1['count']);
        $this->assertEquals(72.0, $p1['avg']);
        $this->assertEquals('note', $p1['latest']);
    }

    public function test_it_buckets_by_week(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-02 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-09 10:00:00',
            'encrypted_values' => ['hr' => 80],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-15'),
            'week'
        );

        $this->assertEquals('week', $result['bucket']);
        $this->assertCount(2, $result['points']);
    }

    public function test_it_buckets_by_month(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-01-15 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-15 10:00:00',
            'encrypted_values' => ['hr' => 80],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-01-01'),
            CarbonImmutable::parse('2026-02-28'),
            'month'
        );

        $this->assertEquals('month', $result['bucket']);
        $this->assertCount(2, $result['points']);
    }

    public function test_it_ignores_unrelated_metrics(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => 70, 'weight' => 180],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-01 23:59:59'),
            'day'
        );

        $this->assertEquals('hr', $result['metric']);
        $this->assertCount(1, $result['points']);
        $this->assertEquals(70.0, $result['points'][0]['avg']);
    }

    public function test_it_excludes_entries_outside_date_range(): void
    {
        $account = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-01-15 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-15 10:00:00',
            'encrypted_values' => ['hr' => 80],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-28'),
            'day'
        );

        $this->assertCount(1, $result['points']);
        $this->assertEquals(80.0, $result['points'][0]['avg']);
    }

    public function test_it_excludes_entries_from_other_accounts(): void
    {
        $account1 = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);
        $account2 = Account::factory()->create(['account_type' => 'User', 'status' => 'ACTIVE']);

        HealthEntry::factory()->create([
            'account_id' => $account1->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);
        HealthEntry::factory()->create([
            'account_id' => $account2->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => 90],
        ]);

        $result = $this->service->timeSeriesForCohort(
            [$account1->id],
            'hr',
            CarbonImmutable::parse('2026-02-01'),
            CarbonImmutable::parse('2026-02-01 23:59:59'),
            'day'
        );

        $this->assertCount(1, $result['points']);
        $this->assertEquals(70.0, $result['points'][0]['avg']);
    }

    public function test_it_includes_from_and_to_in_result(): void
    {
        $from = CarbonImmutable::parse('2026-02-01');
        $to = CarbonImmutable::parse('2026-02-28');

        $result = $this->service->timeSeriesForCohort(
            ['nonexistent-id'],
            'hr',
            $from,
            $to,
            'day'
        );

        $this->assertEquals($from->toIso8601String(), $result['from']);
        $this->assertEquals($to->toIso8601String(), $result['to']);
    }
}

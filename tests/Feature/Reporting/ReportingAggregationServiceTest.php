<?php

namespace Tests\Feature\Reporting;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\ReportingAggregationService;
use Carbon\Carbon;

class ReportingAggregationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_aggregates_numeric_metrics_for_account_in_range(): void
    {
        $account = Account::factory()->create();

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['hr' => 70, 'bp' => 120],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => ['hr' => 90, 'bp' => 130],
        ]);

        $svc = app(ReportingAggregationService::class);

        $out = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00')
        );

        $this->assertEquals(2, $out['hr']['count']);
        $this->assertEquals(70.0, $out['hr']['min']);
        $this->assertEquals(90.0, $out['hr']['max']);
        $this->assertEquals(80.0, $out['hr']['avg']);
        $this->assertEquals(90, $out['hr']['latest']);
    }

    public function test_it_returns_empty_when_no_entries_in_range(): void
    {
        $account = Account::factory()->create([
            //explicitly set ENUM
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $svc = app(ReportingAggregationService::class);

        $out = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00')
        );

        $this->assertSame([], $out);
    }

    public function test_it_ignores_non_numeric_values_for_numeric_aggregates(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        //mix numeric + non-numeric for the same key
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['hr' => 70],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 11:00:00'),
            'encrypted_values' => ['hr' => 'N/A'],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 12:00:00'),
            'encrypted_values' => ['hr' => '90'],
        ]);

        $svc = app(ReportingAggregationService::class);

        $out = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-02 00:00:00')
        );

        $this->assertArrayHasKey('hr', $out);

        $this->assertEquals(2, $out['hr']['count']);

        $this->assertEquals(70.0, $out['hr']['min']);
        $this->assertEquals(90.0, $out['hr']['max']);
        $this->assertEquals(80.0, $out['hr']['avg']);

        //latest is last raw value
        $this->assertEquals('90', $out['hr']['latest']);
        $this->assertEquals('2026-02-01T12:00:00+00:00', $out['hr']['latest_at']);
    }

    public function test_it_respects_only_keys_filter(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => [
                'hr' => 70,
                'bp' => 120,
                'weight' => 170,
            ],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => [
                'hr' => 90,
                'bp' => 130,
                'weight' => 174,
            ],
        ]);

        $svc = app(ReportingAggregationService::class);

        $out = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00'),
            ['hr']
        );

        $this->assertArrayHasKey('hr', $out);
        $this->assertArrayNotHasKey('bp', $out);
        $this->assertArrayNotHasKey('weight', $out);

        $this->assertEquals(2, $out['hr']['count']);
        $this->assertEquals(80.0, $out['hr']['avg']);
    }
}

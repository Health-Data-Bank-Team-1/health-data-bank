<?php

namespace Tests\Feature\Reporting;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Services\PersonalSummaryService;
use Carbon\Carbon;

class PersonalSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_averages_for_each_metric_in_range(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['weight' => 170, 'meals_per_day' => 2],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => ['weight' => 174, 'meals_per_day' => 3],
        ]);

        $svc = app(PersonalSummaryService::class);

        $out = $svc->summaryForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00')
        );

        $this->assertEquals(172.0, $out['averages']['weight']);
        $this->assertEquals(2.5, $out['averages']['meals_per_day']);
        $this->assertEquals(2, $out['counts']['weight']);
        $this->assertEquals(2, $out['counts']['meals_per_day']);
    }

    public function test_it_returns_empty_when_no_entries_in_range(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $svc = app(PersonalSummaryService::class);

        $out = $svc->summaryForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00')
        );

        $this->assertSame([], $out['averages']);
        $this->assertSame([], $out['counts']);
    }

    public function test_it_ignores_non_numeric_values(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['notes' => 'good', 'weight' => 170],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => ['notes' => 'bad', 'weight' => 174],
        ]);

        $svc = app(PersonalSummaryService::class);

        $out = $svc->summaryForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00')
        );

        $this->assertEquals(172.0, $out['averages']['weight']);
        $this->assertEquals(2, $out['counts']['weight']);
    }
}

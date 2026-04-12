<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\User;
use App\Services\ReportingAggregationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummaryConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_me_summary_endpoint_matches_aggregation_service(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-01 10:00:00'),
            'encrypted_values' => ['weight' => 170],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-02-02 10:00:00'),
            'encrypted_values' => ['weight' => 174],
        ]);

        $svc = app(ReportingAggregationService::class);
        $svcResult = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-03 00:00:00'),
            ['weight']
        );

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-02-01&to=2026-02-03&keys=weight'
        );

        $res->assertStatus(200);

        $this->assertEquals($svcResult['weight']['avg'], $res->json('averages.weight'));
        $this->assertEquals($svcResult['weight']['count'], $res->json('counts.weight'));
    }
}

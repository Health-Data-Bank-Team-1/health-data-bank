<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\User;
use App\Services\ReportingAggregationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregationConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_trend_endpoint_matches_aggregation_service(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        //seed data
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => 70],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 18:00:00',
            'encrypted_values' => ['hr' => 90],
        ]);

        //service result
        $svc = app(ReportingAggregationService::class);
        $svcResult = $svc->aggregateForAccount(
            $account->id,
            Carbon::parse('2026-02-01 00:00:00'),
            Carbon::parse('2026-02-02 00:00:00')
        );

        //endpoint result
        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-01&bucket=day'
        );

        $res->assertStatus(200);

        $point = $res->json('points.0');

        //compare values
        $this->assertEquals($svcResult['hr']['count'], $point['count']);
        $this->assertEquals($svcResult['hr']['min'], $point['min']);
        $this->assertEquals($svcResult['hr']['max'], $point['max']);
        $this->assertEquals($svcResult['hr']['avg'], $point['avg']);
    }
}

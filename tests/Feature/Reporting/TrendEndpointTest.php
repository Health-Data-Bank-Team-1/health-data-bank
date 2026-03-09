<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrendEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_daily_buckets_for_metric(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        //Feb 1: two entries
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

        //Feb 2: one entry
        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-02 09:00:00',
            'encrypted_values' => ['hr' => 80],
        ]);

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-02&bucket=day'
        );

        $res->assertStatus(200);

        $json = $res->json();

        $this->assertEquals('hr', $json['metric']);
        $this->assertEquals('day', $json['bucket']);

        $this->assertCount(2, $json['points']);

        //first bucket (Feb 1)
        $p1 = $json['points'][0];
        $this->assertEquals(2, $p1['count']);
        $this->assertEquals(70.0, $p1['min']);
        $this->assertEquals(90.0, $p1['max']);
        $this->assertEquals(80.0, $p1['avg']);
        $this->assertEquals(90, $p1['latest']);

        //second bucket (Feb 2)
        $p2 = $json['points'][1];
        $this->assertEquals(1, $p2['count']);
        $this->assertEquals(80.0, $p2['min']);
        $this->assertEquals(80.0, $p2['max']);
        $this->assertEquals(80.0, $p2['avg']);
        $this->assertEquals(80, $p2['latest']);
    }

    public function test_it_returns_empty_points_when_no_data(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-02&bucket=day'
        );

        $res->assertStatus(200);
        $res->assertJson([
            'metric' => 'hr',
            'bucket' => 'day',
            'points' => [],
        ]);
    }

    public function test_it_ignores_non_numeric_values_for_numeric_aggregates(): void
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
            'timestamp' => '2026-02-01 10:00:00',
            'encrypted_values' => ['hr' => '72'],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => '2026-02-01 11:00:00',
            'encrypted_values' => ['hr' => 'note'], //non-numeric
        ]);

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-01&bucket=day'
        );

        $res->assertStatus(200);

        $p1 = $res->json('points.0');
        $this->assertEquals(1, $p1['count']); //numeric count only
        $this->assertEquals(72.0, $p1['min']);
        $this->assertEquals(72.0, $p1['max']);
        $this->assertEquals(72.0, $p1['avg']);

        $this->assertEquals('note', $p1['latest']);
    }

    public function test_it_validates_inputs(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        //missing metric
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reporting/trends?from=2026-02-01&to=2026-02-02')
            ->assertStatus(422);

        //invalid bucket
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-02&bucket=hour')
            ->assertStatus(422);

        //to must be after from
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reporting/trends?metric=hr&from=2026-02-02&to=2026-02-01&bucket=day')
            ->assertStatus(422);
    }
}

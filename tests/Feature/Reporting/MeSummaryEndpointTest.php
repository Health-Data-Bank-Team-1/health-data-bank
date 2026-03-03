<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeSummaryEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_summary_structure_for_valid_request(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-02-01&to=2026-02-03'
        );

        $res->assertStatus(200);
        $res->assertJsonStructure([
            'averages',
            'counts',
        ]);
        $this->assertIsArray($res->json('averages'));
        $this->assertIsArray($res->json('counts'));
    }

    public function test_returns_filtered_metrics_when_keys_provided(): void
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
            'encrypted_values' => ['hr' => 72, 'weight' => 170],
        ]);

        $res = $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-02-01&to=2026-02-03&keys=hr'
        );

        $res->assertStatus(200);
        $res->assertJsonStructure(['averages', 'counts']);
        $averages = $res->json('averages');
        $this->assertIsArray($averages);
        $this->assertArrayHasKey('hr', $averages);
    }

    public function test_validates_required_params(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/summary')
            ->assertStatus(422);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/summary?from=2026-02-01')
            ->assertStatus(422);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/summary?from=2026-02-03&to=2026-02-01')
            ->assertStatus(422);
    }

    public function test_returns_422_when_user_has_no_account(): void
    {
        $user = User::factory()->create([
            'account_id' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/summary?from=2026-02-01&to=2026-02-03')
            ->assertStatus(422);
    }
}
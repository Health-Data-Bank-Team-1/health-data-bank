<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResearcherAggregatedReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    public function test_researcher_can_generate_aggregated_report(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        $accounts = Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        foreach ($accounts as $index => $account) {
            HealthEntry::factory()->create([
                'account_id' => $account->id,
                'timestamp' => '2026-02-10 10:00:00',
                'encrypted_values' => [
                    'hr' => 70 + $index,
                    'weight' => 150 + $index,
                ],
            ]);
        }

        $cohortId = Str::uuid()->toString();

        DB::table('researcher_cohorts')->insert([
            'id' => $cohortId,
            'name' => 'Test Cohort',
            'purpose' => 'Testing aggregated reporting',
            'filters_json' => json_encode([
                'account_type' => 'User',
                'account_status' => 'ACTIVE',
            ]),
            'estimated_size' => 10,
            'version' => 1,
            'created_by' => $researcherAccount->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/reports/aggregated', [
            'cohort_id' => $cohortId,
            'from' => '2026-02-01',
            'to' => '2026-02-28',
            'keys' => 'hr,weight',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'cohort_id',
                    'cohort_name',
                    'cohort_size',
                    'from',
                    'to',
                    'metrics',
                ],
            ]);

        $response->assertJsonPath('data.cohort_id', $cohortId);
        $response->assertJsonPath('data.cohort_size', 10);
        $response->assertJsonPath('data.metrics.hr.count', 10);
        $response->assertJsonPath('data.metrics.hr.avg', 74.5);
        $response->assertJsonPath('data.metrics.weight.count', 10);
        $response->assertJsonPath('data.metrics.weight.avg', 154.5);
    }

    public function test_guest_cannot_access_aggregated_report(): void
    {
        $response = $this->postJson('/api/researcher/reports/aggregated', [
            'cohort_id' => (string) Str::uuid(),
            'from' => '2026-02-01',
            'to' => '2026-02-28',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_researcher_cannot_access_aggregated_report(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/reports/aggregated', [
            'cohort_id' => (string) Str::uuid(),
            'from' => '2026-02-01',
            'to' => '2026-02-28',
        ]);

        $response->assertStatus(403);
    }

    public function test_invalid_cohort_returns_404(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/reports/aggregated', [
            'cohort_id' => (string) Str::uuid(),
            'from' => '2026-02-01',
            'to' => '2026-02-28',
        ]);

        $response->assertStatus(404);
    }
}

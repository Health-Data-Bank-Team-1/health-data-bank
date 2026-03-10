<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AggregatedMetricsServiceTest extends TestCase
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

    public function test_it_returns_count_and_average_for_cohort_metrics(): void
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
                    'hr' => 70 + $index, // 70..79 => avg 74.5
                    'weight' => 150 + $index, // 150..159 => avg 154.5
                ],
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')->getJson(
            '/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-28&account_type=User&account_status=ACTIVE'
        );

        $response->assertStatus(200);

        $response->assertJsonPath('cohort_size', 10);
        $response->assertJsonPath('metrics.hr.count', 10);
        $response->assertJsonPath('metrics.hr.avg', 74.5);
        $response->assertJsonPath('metrics.weight.count', 10);
        $response->assertJsonPath('metrics.weight.avg', 154.5);
    }

    public function test_it_respects_requested_keys(): void
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

        foreach ($accounts as $account) {
            HealthEntry::factory()->create([
                'account_id' => $account->id,
                'timestamp' => '2026-02-10 10:00:00',
                'encrypted_values' => [
                    'hr' => 80,
                    'weight' => 170,
                ],
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')->getJson(
            '/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-28&account_type=User&account_status=ACTIVE&keys=hr'
        );

        $response->assertStatus(200);

        $metrics = $response->json('metrics');

        $this->assertArrayHasKey('hr', $metrics);
        $this->assertArrayNotHasKey('weight', $metrics);
    }

    public function test_it_ignores_non_numeric_values(): void
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
                    'notes' => 'not numeric',
                ],
            ]);
        }

        $response = $this->actingAs($user, 'sanctum')->getJson(
            '/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-28&account_type=User&account_status=ACTIVE'
        );

        $response->assertStatus(200);

        $metrics = $response->json('metrics');

        $this->assertArrayHasKey('hr', $metrics);
        $this->assertArrayNotHasKey('notes', $metrics);
    }
}

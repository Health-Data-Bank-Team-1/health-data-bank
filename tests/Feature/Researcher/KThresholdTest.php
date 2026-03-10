<?php

namespace Tests\Feature\Researcher;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KThresholdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'researcher', 'guard_name' => 'web']);
    }

    private function createResearcher(): User
    {
        $account = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $user->assignRole('researcher');

        return $user;
    }

    public function test_researcher_aggregate_report_is_suppressed_when_cohort_size_is_below_ten(): void
    {
        $researcher = $this->createResearcher();

        Account::factory()->count(9)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertStatus(422);
    }

    public function test_researcher_aggregate_report_is_allowed_when_cohort_size_is_exactly_ten(): void
    {
        $researcher = $this->createResearcher();

        Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertOk()
            ->assertJsonPath('cohort_size', 10);
    }

    public function test_researcher_aggregate_report_is_allowed_when_cohort_size_is_above_ten(): void
    {
        $researcher = $this->createResearcher();

        Account::factory()->count(12)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertOk()
            ->assertJsonPath('cohort_size', 12);
    }
}
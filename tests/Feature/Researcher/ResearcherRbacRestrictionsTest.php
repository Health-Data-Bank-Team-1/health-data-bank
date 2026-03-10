<?php

namespace Tests\Feature\Researcher;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResearcherRbacRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'researcher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'provider', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
    }

    private function createActor(string $role, string $accountType): User
    {
        $account = Account::factory()->create([
            'account_type' => $accountType,
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $user->assignRole($role);

        return $user;
    }

    public function test_researcher_cannot_access_admin_forms_index(): void
    {
        $researcher = $this->createActor('researcher', 'Researcher');

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/admin/forms')
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_forms_index(): void
    {
        $admin = $this->createActor('admin', 'Admin');

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/forms')
            ->assertOk();
    }

    public function test_researcher_can_access_me_summary_endpoint(): void
    {
        $researcher = $this->createActor('researcher', 'Researcher');

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/me/summary?from=2026-03-01&to=2026-03-10&keys=hr')
            ->assertOk();
    }

    public function test_guest_cannot_access_admin_forms_index(): void
    {
        $this->getJson('/api/admin/forms')
            ->assertStatus(401);
    }

    public function test_guest_cannot_access_me_summary_endpoint(): void
    {
        $this->getJson('/api/me/summary?from=2026-03-01&to=2026-03-10&keys=hr')
            ->assertStatus(401);
    }

    public function test_researcher_can_access_researcher_aggregate_reporting_endpoint(): void
    {
        $researcher = $this->createActor('researcher', 'Researcher');

        Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($researcher, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertOk();
    }

    public function test_provider_cannot_access_researcher_aggregate_reporting_endpoint(): void
    {
        $provider = $this->createActor('provider', 'HealthcareProvider');

        Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($provider, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertStatus(403);
    }

    public function test_standard_user_cannot_access_researcher_aggregate_reporting_endpoint(): void
    {
        $user = $this->createActor('user', 'User');

        Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertStatus(403);
    }

    public function test_guest_cannot_access_researcher_aggregate_reporting_endpoint(): void
    {
        $this->getJson('/api/research/reporting/aggregate?from=2026-03-01&to=2026-03-10&account_type=User')
            ->assertStatus(401);
    }
}
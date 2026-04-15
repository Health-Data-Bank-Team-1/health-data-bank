<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['admin', 'researcher', 'provider', 'user'] as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['id' => (string) Str::uuid()]
            );
        }
    }

    private function createUserWithRole(string $role): User
    {
        $accountType = match ($role) {
            'admin' => 'Admin',
            'researcher' => 'Researcher',
            'provider' => 'HealthcareProvider',
            default => 'User',
        };

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

    public function test_admin_endpoints_require_admin_role(): void
    {
        $this->getJson('/api/admin/forms')->assertStatus(401);

        foreach (['user', 'researcher', 'provider'] as $role) {
            $user = $this->createUserWithRole($role);
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/admin/forms')
                ->assertStatus(403);
        }

        $admin = $this->createUserWithRole('admin');
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/forms');

        $this->assertNotContains($response->status(), [401, 403]);
    }

    public function test_researcher_endpoints_require_researcher_role(): void
    {
        $this->getJson('/api/research/reporting/aggregate')->assertStatus(401);

        foreach (['user', 'admin', 'provider'] as $role) {
            $user = $this->createUserWithRole($role);
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/research/reporting/aggregate')
                ->assertStatus(403);
        }

        $researcher = $this->createUserWithRole('researcher');
        $response = $this->actingAs($researcher, 'sanctum')->getJson('/api/research/reporting/aggregate');

        $this->assertNotContains($response->status(), [401, 403]);
    }

    public function test_provider_endpoints_require_provider_role(): void
    {
        $this->getJson('/api/provider/patients/search')->assertStatus(401);

        foreach (['user', 'admin', 'researcher'] as $role) {
            $user = $this->createUserWithRole($role);
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/provider/patients/search')
                ->assertStatus(403);
        }

        $provider = $this->createUserWithRole('provider');
        $response = $this->actingAs($provider, 'sanctum')->getJson('/api/provider/patients/search');

        $this->assertNotContains($response->status(), [401, 403]);
    }
}

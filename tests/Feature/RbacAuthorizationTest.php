<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $researcherRole;
    protected Role $userRole;
    protected Role $providerRole;

    protected function setUp(): void
    {
        parent::setUp();

        // Create official roles
        $this->adminRole = Role::create(['name' => 'Administrator']);
        $this->researcherRole = Role::create(['name' => 'Researcher']);
        $this->userRole = Role::create(['name' => 'User']);
        $this->providerRole = Role::create(['name' => 'Healthcare Provider']);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('dashboard.admin'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create([
            'role_id' => $this->userRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('dashboard.admin'));

        $response->assertForbidden();
    }

    public function test_researcher_can_access_researcher_dashboard()
    {
        $researcher = User::factory()->create([
            'role_id' => $this->researcherRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($researcher)
                         ->get(route('dashboard.researcher'));

        $response->assertOk();
    }

    public function test_user_cannot_access_researcher_dashboard()
    {
        $user = User::factory()->create([
            'role_id' => $this->userRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('dashboard.researcher'));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->get(route('dashboard.admin'));

        $response->assertRedirect('/login');
    }
}
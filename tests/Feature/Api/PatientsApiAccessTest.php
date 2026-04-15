<?php

namespace Tests\Feature\Api;

use App\Models\Account;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientsApiAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    public function test_guest_cannot_access_patients_resource(): void
    {
        $this->getJson('/api/patients')->assertStatus(401);
    }

    public function test_non_admin_cannot_access_patients_resource(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/patients')
            ->assertStatus(403);
    }

    public function test_admin_can_access_patients_resource(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);
        $user->assignRole('admin');

        Patient::query()->create([
            'name' => 'Example Patient',
            'email' => 'patient@example.com',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/patients')
            ->assertOk();
    }
}

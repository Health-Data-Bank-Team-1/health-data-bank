<?php

namespace Tests\Feature\Provider;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'provider', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    public function test_non_provider_is_denied(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/provider/patients/search')
            ->assertStatus(403);
    }

    public function test_provider_can_search_patients(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        Account::factory()->create([
            'account_type' => 'User',
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'ACTIVE',
        ]);

        Account::factory()->create([
            'account_type' => 'User',
            'name' => 'John Smith',
            'email' => 'john@example.com',
            'status' => 'DEACTIVATED',
        ]);

        Account::factory()->create([
            'account_type' => 'Researcher',
            'name' => 'Research User',
            'email' => 'research@example.com',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($providerUser, 'sanctum')
            ->getJson('/api/provider/patients/search?q=Jane');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Jane Doe');
        $response->assertJsonPath('data.0.email', 'jane@example.com');
    }

    public function test_provider_can_filter_by_status(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        Account::factory()->create([
            'account_type' => 'User',
            'name' => 'Active Patient',
            'email' => 'active@example.com',
            'status' => 'ACTIVE',
        ]);

        Account::factory()->create([
            'account_type' => 'User',
            'name' => 'Inactive Patient',
            'email' => 'inactive@example.com',
            'status' => 'DEACTIVATED',
        ]);

        $response = $this->actingAs($providerUser, 'sanctum')
            ->getJson('/api/provider/patients/search?status=ACTIVE');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Active Patient');
    }
}

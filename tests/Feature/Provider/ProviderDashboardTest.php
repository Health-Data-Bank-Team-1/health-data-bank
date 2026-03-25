<?php

namespace Tests\Feature\Provider;

use App\Livewire\Dashboards\ProviderDashboard;
use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use Livewire\Livewire;

class ProviderDashboardTest extends TestCase
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
            ->getJson('/api/provider/dashboard')
            ->assertStatus(403);
    }

    public function test_provider_gets_dashboard_totals(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $patient1 = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $patient2 = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $patient3 = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'DEACTIVATED',
        ]);

        //non patient account shouldn't count toward patient totals
        $researcher = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $patient1->id,
            'timestamp' => '2026-02-10 10:00:00',
            'encrypted_values' => ['hr' => 72],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $patient2->id,
            'timestamp' => '2026-02-11 10:00:00',
            'encrypted_values' => ['hr' => 75],
        ]);

        //second entry for same patient shouldn't increase distinct count
        HealthEntry::factory()->create([
            'account_id' => $patient2->id,
            'timestamp' => '2026-02-12 10:00:00',
            'encrypted_values' => ['hr' => 78],
        ]);

        //non-patient health entry shouldn't count
        HealthEntry::factory()->create([
            'account_id' => $researcher->id,
            'timestamp' => '2026-02-13 10:00:00',
            'encrypted_values' => ['hr' => 81],
        ]);

        $response = $this->actingAs($providerUser, 'sanctum')
            ->getJson('/api/provider/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('totals.patients', 3);
        $response->assertJsonPath('totals.active_patients', 2);
        $response->assertJsonPath('totals.deactivated_patients', 1);
        $response->assertJsonPath('totals.patients_with_health_entries', 2);
    }


    public function test_the_component_can_render()
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $this->actingAs($providerUser);
        $component = Livewire::test(ProviderDashboard::class);
        $component->assertStatus(200);
    }

    public function test_elements_are_present()
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $this->actingAs($providerUser);
        Livewire::test(ProviderDashboard::class)
            ->assertSee('Patients')
            ->assertSee('Reports');
    }
}

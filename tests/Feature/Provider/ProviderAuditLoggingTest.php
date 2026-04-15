<?php

namespace Tests\Feature\Provider;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ProviderAuditLoggingTest extends TestCase
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

    public function test_provider_dashboard_access_writes_audit_row(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $this->actingAs($providerUser, 'sanctum')
            ->getJson('/api/provider/dashboard')
            ->assertStatus(200);

        $this->assertDatabaseHas('audits', [
            'event' => 'provider_dashboard_view',
        ]);
    }

    public function test_provider_patient_search_writes_audit_row(): void
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

        $this->actingAs($providerUser, 'sanctum')
            ->getJson('/api/provider/patients/search?q=Jane')
            ->assertStatus(200);

        $this->assertDatabaseHas('audits', [
            'event' => 'provider_patient_search',
        ]);
    }

    public function test_provider_patient_record_access_writes_audit_row(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $patient = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        // Link provider account to patient account
        $patient->providers()->attach($providerAccount->id);

        HealthEntry::factory()->create([
            'account_id' => $patient->id,
            'timestamp' => '2026-02-10 10:00:00',
            'encrypted_values' => ['hr' => 72],
        ]);

        $this->actingAs($providerUser, 'sanctum')
            ->getJson("/api/provider/patients/{$patient->id}/record")
            ->assertStatus(200);

        $this->assertDatabaseHas('audits', [
            'event' => 'provider_patient_record_view',
        ]);
    }
}

<?php

namespace Tests\Feature\Provider;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PatientRecordTest extends TestCase
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
        $patient = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/provider/patients/{$patient->id}/record")
            ->assertStatus(403);
    }

    public function test_provider_can_retrieve_patient_record(): void
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
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'status' => 'ACTIVE',
        ]);

        HealthEntry::factory()->create([
            'account_id' => $patient->id,
            'timestamp' => '2026-02-10 10:00:00',
            'encrypted_values' => [
                'hr' => 72,
                'weight' => 165,
            ],
        ]);

        $response = $this->actingAs($providerUser, 'sanctum')
            ->getJson("/api/provider/patients/{$patient->id}/record");

        $response->assertStatus(200);
        $response->assertJsonPath('patient.name', 'Jane Doe');
        $response->assertJsonPath('patient.email', 'jane@example.com');
        $response->assertJsonCount(1, 'health_entries');
        $response->assertJsonPath('health_entries.0.encrypted_values.hr', 72);
    }

    public function test_provider_gets_404_for_non_patient_account(): void
    {
        $providerAccount = Account::factory()->create([
            'account_type' => 'HealthcareProvider',
            'status' => 'ACTIVE',
        ]);

        $providerUser = User::factory()->create([
            'account_id' => $providerAccount->id,
        ]);

        $providerUser->assignRole('provider');

        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($providerUser, 'sanctum')
            ->getJson("/api/provider/patients/{$researcherAccount->id}/record")
            ->assertStatus(404)
            ->assertJson([
                'message' => 'Patient not found.',
            ]);
    }
}

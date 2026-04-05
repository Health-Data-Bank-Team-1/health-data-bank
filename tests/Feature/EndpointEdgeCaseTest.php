<?php

namespace Tests\Feature\Polish;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class EndpointEdgeCaseTest extends TestCase
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

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    public function test_provider_search_returns_empty_data_when_no_patients_exist(): void
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
            ->getJson('/api/provider/patients/search?q=Nobody')
            ->assertStatus(200)
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_provider_search_validates_status_filter(): void
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
            ->getJson('/api/provider/patients/search?status=INVALID')
            ->assertStatus(422);
    }

    public function test_provider_record_returns_empty_health_entries_for_patient_with_no_entries(): void
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

        $this->actingAs($providerUser, 'sanctum')
            ->getJson("/api/provider/patients/{$patient->id}/record")
            ->assertStatus(200)
            ->assertJson([
                'health_entries' => [],
            ]);
    }

    public function test_dashboard_trends_export_validates_date_range(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?date_from=2026-03-10&date_to=2026-03-01')
            ->assertStatus(302);
    }

    public function test_researcher_aggregate_validates_account_status_filter(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $researcherUser = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $researcherUser->assignRole('researcher');

        $this->actingAs($researcherUser, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-28&account_status=INVALID')
            ->assertStatus(422);
    }

    public function test_researcher_aggregate_suppresses_when_cohort_is_empty(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $researcherUser = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $researcherUser->assignRole('researcher');

        $this->actingAs($researcherUser, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-28&account_type=User&account_status=ACTIVE')
            ->assertStatus(422)
            ->assertJson([
                'error' => 'CohortSuppressed',
            ]);
    }
}

<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResearcherCohortEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Role::firstOrCreate(
            ['name' => 'researcher', 'guard_name' => 'web'],
            ['id' => (string) Str::uuid()]
        );
    }

    public function test_researcher_can_create_cohort_when_threshold_is_met(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Active User Cohort',
            'purpose' => 'Used for aggregated reporting',
            'account_type' => 'User',
            'account_status' => 'ACTIVE',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'purpose',
                    'filters',
                    'estimated_cohort_size',
                    'minimum_required',
                    'version',
                    'saved',
                ],
            ]);

        $this->assertDatabaseHas('researcher_cohorts', [
            'name' => 'Active User Cohort',
            'purpose' => 'Used for aggregated reporting',
        ]);
    }

    public function test_guest_cannot_access_cohort_endpoint(): void
    {
        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Test Cohort',
            'purpose' => 'Testing guest access',
            'account_type' => 'User',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_researcher_cannot_access_cohort_endpoint(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Admin Test Cohort',
            'purpose' => 'Should be forbidden',
            'account_type' => 'User',
        ]);

        $response->assertStatus(403);
    }

    public function test_invalid_date_range_fails_validation(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Invalid Date Cohort',
            'purpose' => 'Validation test',
            'account_type' => 'User',
            'created_from' => '2026-03-10',
            'created_to' => '2026-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['created_to']);
    }

    public function test_cohort_creation_fails_when_k_threshold_is_not_met(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        Account::factory()->count(2)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Too Small Cohort',
            'purpose' => 'Threshold test',
            'account_type' => 'User',
            'account_status' => 'ACTIVE',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cohort does not meet minimum anonymity size.',
            ]);
    }
}

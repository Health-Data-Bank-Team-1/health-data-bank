<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

        $accounts = Account::factory()->count(10)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        foreach ($accounts as $account) {
            DB::table('participant_profiles')->insert([
                'account_id' => $account->id,
                'gender' => 'female',
                'date_of_birth' => '2000-01-01',
                'location' => 'PEI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'PEI Women 18-30',
            'purpose' => 'Aggregate wellness trends',
            'gender' => 'female',
            'location' => 'PEI',
            'age_min' => 18,
            'age_max' => 30,
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
            'name' => 'PEI Women 18-30',
            'purpose' => 'Aggregate wellness trends',
            'estimated_size' => 10,
            'version' => 1,
        ]);
    }

    public function test_guest_cannot_access_cohort_endpoint(): void
    {
        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Test Cohort',
            'purpose' => 'Testing guest access',
            'gender' => 'female',
            'location' => 'PEI',
            'age_min' => 18,
            'age_max' => 30,
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
            'gender' => 'female',
            'location' => 'PEI',
            'age_min' => 18,
            'age_max' => 30,
        ]);

        $response->assertStatus(403);
    }

    public function test_invalid_age_range_fails_validation(): void
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
            'name' => 'Invalid Age Cohort',
            'purpose' => 'Validation test',
            'gender' => 'female',
            'location' => 'PEI',
            'age_min' => 40,
            'age_max' => 18,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age_max']);
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

        $accounts = Account::factory()->count(2)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        foreach ($accounts as $account) {
            DB::table('participant_profiles')->insert([
                'account_id' => $account->id,
                'gender' => 'female',
                'date_of_birth' => '2000-01-01',
                'location' => 'PEI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'name' => 'Too Small Cohort',
            'purpose' => 'Threshold test',
            'gender' => 'female',
            'location' => 'PEI',
            'age_min' => 18,
            'age_max' => 30,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cohort does not meet minimum anonymity size.',
            ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResearcherCohortEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_researcher_can_access_cohort_endpoint(): void
    {
        $user = User::factory()->create([
            'role' => 'researcher',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'age_min' => 18,
            'age_max' => 40,
        ]);

        $response->assertStatus(200);
    }

    public function test_guest_cannot_access_cohort_endpoint(): void
    {
        $response = $this->postJson('/api/researcher/cohorts', []);

        $response->assertStatus(401);
    }

    public function test_non_researcher_cannot_access_cohort_endpoint(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', []);

        $response->assertStatus(403);
    }

    public function test_invalid_age_range_fails_validation(): void
    {
        $user = User::factory()->create([
            'role' => 'researcher',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'age_min' => 50,
            'age_max' => 20,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['age_max']);
    }

    public function test_invalid_date_range_fails_validation(): void
    {
        $user = User::factory()->create([
            'role' => 'researcher',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/cohorts', [
            'start_date' => '2026-03-10',
            'end_date' => '2026-01-01',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }
}

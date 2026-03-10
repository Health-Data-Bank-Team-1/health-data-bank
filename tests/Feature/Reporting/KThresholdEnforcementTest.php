<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class KThresholdEnforcementTest extends TestCase
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

    public function test_cohort_below_threshold_is_suppressed(): void
    {
        $researcherAccount = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $researcherAccount->id,
        ]);

        $user->assignRole('researcher');

        Account::factory()->count(5)->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-01&account_type=User&account_status=ACTIVE')
            ->assertStatus(422)
            ->assertJson([
                'error' => 'CohortSuppressed',
            ]);
    }

    public function test_cohort_at_threshold_is_allowed(): void
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

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-01&account_type=User&account_status=ACTIVE')
            ->assertStatus(200)
            ->assertJson([
                'cohort_size' => 10,
            ]);
    }
}

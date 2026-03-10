<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResearcherAccessTest extends TestCase
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

    public function test_non_researcher_is_denied(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-01')
            ->assertStatus(403);
    }

    public function test_researcher_is_allowed_past_role_check(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Researcher',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $user->assignRole('researcher');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/research/reporting/aggregate?from=2026-02-01&to=2026-02-01')
            ->assertStatus(422);
    }
}

<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\HealthEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ResearcherAuditLogTest extends TestCase
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

    public function test_audit_log_written_for_cohort_creation(): void
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

        $this->postJson('/api/researcher/cohorts', [
            'name' => 'Audit Cohort',
            'purpose' => 'Audit test',
            'account_type' => 'User',
            'account_status' => 'ACTIVE',
        ])->assertStatus(201);

        $this->assertDatabaseHas('audits', [
            'event' => 'researcher_cohort_created',
        ]);
    }

    public function test_audit_log_written_for_aggregated_report_view(): void
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

        foreach ($accounts as $index => $account) {
            HealthEntry::factory()->create([
                'account_id' => $account->id,
                'timestamp' => '2026-02-10 10:00:00',
                'encrypted_values' => [
                    'hr' => 70 + $index,
                ],
            ]);
        }

        $cohortId = Str::uuid()->toString();

        DB::table('researcher_cohorts')->insert([
            'id' => $cohortId,
            'name' => 'Audit Report Cohort',
            'purpose' => 'Audit report test',
            'filters_json' => json_encode([
                'account_type' => 'User',
                'account_status' => 'ACTIVE',
            ]),
            'estimated_size' => 10,
            'version' => 1,
            'created_by' => $researcherAccount->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/researcher/reports/aggregated', [
            'cohort_id' => $cohortId,
            'from' => '2026-02-01',
            'to' => '2026-02-28',
            'keys' => 'hr',
        ])->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'aggregated_report_viewed',
        ]);
    }

    public function test_audit_log_written_for_csv_export(): void
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

        foreach ($accounts as $index => $account) {
            HealthEntry::factory()->create([
                'account_id' => $account->id,
                'timestamp' => '2026-02-10 10:00:00',
                'encrypted_values' => [
                    'hr' => 70 + $index,
                ],
            ]);
        }

        $cohortId = Str::uuid()->toString();

        DB::table('researcher_cohorts')->insert([
            'id' => $cohortId,
            'name' => 'Audit Export Cohort',
            'purpose' => 'Audit export test',
            'filters_json' => json_encode([
                'account_type' => 'User',
                'account_status' => 'ACTIVE',
            ]),
            'estimated_size' => 10,
            'version' => 1,
            'created_by' => $researcherAccount->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->post('/api/researcher/reports/aggregated/export.csv', [
            'cohort_id' => $cohortId,
            'from' => '2026-02-01',
            'to' => '2026-02-28',
            'keys' => 'hr',
        ])->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'aggregated_report_exported',
        ]);
    }
}

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

class ResearcherReportExportTest extends TestCase
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

    public function test_researcher_can_export_aggregated_report_csv(): void
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
                    'weight' => 150 + $index,
                ],
            ]);
        }

        $cohortId = Str::uuid()->toString();

        DB::table('researcher_cohorts')->insert([
            'id' => $cohortId,
            'name' => 'Export Cohort',
            'purpose' => 'CSV export testing',
            'filters_json' => json_encode([
                'account_type' => 'User',
                'account_status' => 'ACTIVE'
            ]),
            'estimated_size' => 10,
            'version' => 1,
            'created_by' => $researcherAccount->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->post(
            '/api/researcher/reports/aggregated/export.csv',
            [
                'cohort_id' => $cohortId,
                'from' => '2026-02-01',
                'to' => '2026-02-28',
                'keys' => 'hr,weight',
            ]
        );

        $response->assertOk();

        $response->assertHeader('content-type', 'text/csv; charset=utf-8');
        $content = $response->streamedContent();

        $this->assertStringContainsString('metric_key,count,avg', $content);
        $this->assertStringContainsString('hr,10,74.5', $content);
        $this->assertStringContainsString('weight,10,154.5', $content);
    }

    public function test_guest_cannot_export_aggregated_report(): void
    {
        $response = $this->postJson('/api/researcher/reports/aggregated/export.csv', [
            'cohort_id' => (string) Str::uuid(),
            'from' => '2026-02-01',
            'to' => '2026-02-28',
        ]);

        $response->assertStatus(401);
    }

    public function test_non_researcher_cannot_export_aggregated_report(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/researcher/reports/aggregated/export.csv', [
            'cohort_id' => (string) Str::uuid(),
            'from' => '2026-02-01',
            'to' => '2026-02-28',
        ]);

        $response->assertStatus(403);
    }
}

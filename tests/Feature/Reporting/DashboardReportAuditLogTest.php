<?php

namespace Tests\Feature\Reports;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardReportAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_trends_view_writes_audit_row(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('form_submissions')->insert([
            [
                'id' => (string) Str::uuid(),
                'account_id' => $account->id,
                'form_template_id' => null,
                'status' => 'SUBMITTED',
                'submitted_at' => '2026-03-01 10:00:00',
            ],
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?group_by=day&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(200);

        $this->assertDatabaseHas('audits', [
            'event' => 'dashboard_trends_view_requested',
        ]);
    }

    public function test_dashboard_trends_export_writes_audit_row(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('form_submissions')->insert([
            [
                'id' => (string) Str::uuid(),
                'account_id' => $account->id,
                'form_template_id' => null,
                'status' => 'SUBMITTED',
                'submitted_at' => '2026-03-01 10:00:00',
            ],
        ]);

        $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(200);

        $this->assertDatabaseHas('audits', [
            'event' => 'dashboard_trends_export_requested',
        ]);
    }
}

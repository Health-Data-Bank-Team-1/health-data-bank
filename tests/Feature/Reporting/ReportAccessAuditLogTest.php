<?php

namespace Tests\Feature\Reporting;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Account;

class ReportAccessAuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_trends_endpoint_writes_audit_row(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')->getJson(
            '/api/reporting/trends?metric=hr&from=2026-02-01&to=2026-02-01&bucket=day'
        )->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'reporting_trends_view',
            'user_id' => $account->id,
        ]);
    }

    public function test_summary_endpoint_writes_audit_row(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-02-01&to=2026-02-03&keys=hr'
        )->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'reporting_summary_view',
            'user_id' => $account->id,
        ]);
    }
}

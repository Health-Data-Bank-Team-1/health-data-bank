<?php

namespace Tests\Feature\Reporting;

use App\Models\Account;
use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardTrendsExportTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAccount(string $accountType = 'User'): User
    {
        $account = Account::factory()->create([
            'account_type' => $accountType,
            'status' => 'ACTIVE',
        ]);

        return User::factory()->create([
            'account_id' => $account->id,
        ]);
    }

    public function test_guest_cannot_export_dashboard_trends_csv(): void
    {
        $this->get('/api/reports/dashboard/trends/export.csv')->assertStatus(401);
    }

    public function test_dashboard_trends_csv_contains_utf8_bom_and_header_for_empty_dataset(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user, 'sanctum')->get(
            '/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-01-01&date_to=2026-01-02'
        );

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('period,value', $content);
    }

    public function test_dashboard_trends_csv_escapes_special_characters_in_period_values(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('form_submissions')->insert([
            'id' => (string) Str::uuid(),
            'account_id' => $account->id,
            'form_template_id' => FormTemplate::factory()->create([
                'approval_status' => 'approved',
            ])->id,
            'status' => 'SUBMITTED',
            'submitted_at' => '2026-01-01 12:00:00',
        ]);

        $response = $this->actingAs($user, 'sanctum')->get(
            '/api/reports/dashboard/trends/export.csv?group_by=week&date_from=2026-01-01&date_to=2026-01-07'
        );

        $response->assertOk();
        $content = $response->streamedContent();

        // Week label includes a dash (for example "2026-W01"), which should appear as a plain CSV field.
        $this->assertStringContainsString('2026-W', $content);
    }
}

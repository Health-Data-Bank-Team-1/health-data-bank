<?php

namespace Tests\Feature\Reports;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard_trends(): void
    {
        $this->getJson('/api/reports/dashboard/trends')
            ->assertStatus(401);
    }

    public function test_dashboard_trends_returns_grouped_json(): void
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
            [
                'id' => (string) Str::uuid(),
                'account_id' => $account->id,
                'form_template_id' => null,
                'status' => 'SUBMITTED',
                'submitted_at' => '2026-03-01 12:00:00',
            ],
            [
                'id' => (string) Str::uuid(),
                'account_id' => $account->id,
                'form_template_id' => null,
                'status' => 'SUBMITTED',
                'submitted_at' => '2026-03-02 09:00:00',
            ],
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?group_by=day&date_from=2026-03-01&date_to=2026-03-02');

        $response->assertStatus(200);
        $response->assertJsonPath('metric', 'submission_count');
        $response->assertJsonPath('group_by', 'day');
        $response->assertJsonPath('date_from', '2026-03-01');
        $response->assertJsonPath('date_to', '2026-03-02');
        $response->assertJsonPath('labels.0', '2026-03-01');
        $response->assertJsonPath('values.0', 2);
        $response->assertJsonPath('labels.1', '2026-03-02');
        $response->assertJsonPath('values.1', 1);
    }

    public function test_dashboard_trends_returns_empty_arrays_for_no_data(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?group_by=day&date_from=2026-03-01&date_to=2026-03-02');

        $response->assertStatus(200);
        $response->assertJsonPath('labels', []);
        $response->assertJsonPath('values', []);
    }

    public function test_dashboard_trends_export_returns_csv(): void
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

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-03-01&date_to=2026-03-02');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_dashboard_trends_validates_date_range(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?date_from=2026-03-10&date_to=2026-03-01')
            ->assertStatus(422);
    }

    public function test_dashboard_trends_export_returns_empty_csv_when_no_data(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-03-01&date_to=2026-03-02');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('period,value', $content);
    }

    public function test_dashboard_trends_returns_empty_when_form_template_id_matches_no_rows(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?group_by=day&date_from=2026-03-01&date_to=2026-03-02&form_template_id=nonexistent-template-id');

        $response->assertStatus(200);
        $response->assertJsonPath('labels', []);
        $response->assertJsonPath('values', []);
    }

    public function test_dashboard_trends_export_returns_empty_csv_when_form_template_id_matches_no_rows(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-03-01&date_to=2026-03-02&form_template_id=nonexistent-template-id');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();
        $this->assertStringContainsString('period,value', $content);
    }

    public function test_dashboard_trends_rejects_unsupported_metric(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?metric=bad_metric&group_by=day&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(422);
    }

    public function test_dashboard_trends_rejects_unsupported_group_by(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?metric=submission_count&group_by=year&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(422);
    }

    public function test_dashboard_trends_returns_403_when_user_has_no_account_mapping(): void
    {
        $user = User::factory()->create([
            'account_id' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/reports/dashboard/trends?group_by=day&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(403);
    }

    public function test_dashboard_trends_export_returns_403_when_user_has_no_account_mapping(): void
    {
        $user = User::factory()->create([
            'account_id' => null,
        ]);

        $this->actingAs($user, 'sanctum')
            ->get('/api/reports/dashboard/trends/export.csv?group_by=day&date_from=2026-03-01&date_to=2026-03-02')
            ->assertStatus(403);
    }
}

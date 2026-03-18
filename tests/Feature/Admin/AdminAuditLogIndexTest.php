<?php

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAuditLogIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_audits(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'login_success',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => null,
                'url' => '/login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['auth','outcome:success']",
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'researcher_aggregated_report_exported',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => "{'format':'csv'}",
                'url' => '/api/researcher/reports/aggregated/export.csv',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['reporting','researcher','outcome:success','format:csv']",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/audit-log')
            ->assertOk()
            ->assertJsonStructure([
                'current_page',
                'data' => [
                    '*' => [
                        'id',
                        'event',
                        'user_type',
                        'user_id',
                        'auditable_type',
                        'auditable_id',
                        'old_values',
                        'new_values',
                        'url',
                        'ip_address',
                        'user_agent',
                        'tags',
                        'created_at',
                    ]
                ],
            ]);
    }

    public function test_admin_can_filter_by_event(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'login_failure',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => "{'reason':'invalid_credentials'}",
                'url' => '/login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['auth','outcome:failure']",
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'logout',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => null,
                'url' => '/logout',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['auth','outcome:success']",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/audit-log?event=login_failure')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('login_failure', $response->json('data.0.event'));
    }

    public function test_admin_can_filter_by_tag(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'account_id' => $account->id,
        ]);

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'reporting_trends_view',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => "{'metric':'steps'}",
                'url' => '/api/reporting/trends',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['reporting','resource:trends']",
                'created_at' => now()->subMinute(),
                'updated_at' => now()->subMinute(),
            ],
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'login_success',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => null,
                'url' => '/login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => "['auth','outcome:success']",
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/admin/audit-log?tag=reporting')
            ->assertOk();

        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('reporting_trends_view', $response->json('data.0.event'));
    }

    public function test_non_admin_cannot_list_audits(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/audit-log')
            ->assertForbidden();
    }
}

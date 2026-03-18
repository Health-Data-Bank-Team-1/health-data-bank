<?php

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAuditLogExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_audits_as_csv(): void
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->get('/api/admin/audit-log/export.csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $content = $response->streamedContent();

        $this->assertStringContainsString('event,user_type,user_id', $content);
        $this->assertStringContainsString('login_success', $content);
    }

    public function test_admin_can_export_filtered_audits_as_csv(): void
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
            ->get('/api/admin/audit-log/export.csv?event=logout');

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('logout', $content);
        $this->assertStringNotContainsString('login_failure', $content);
    }

    public function test_non_admin_cannot_export_audits(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->get('/api/admin/audit-log/export.csv')
            ->assertForbidden();
    }
}

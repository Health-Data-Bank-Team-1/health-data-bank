<?php

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuditLogExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::where('name', 'admin')->where('guard_name', 'web')->first();
        if (! $adminRole) {
            $adminRole = new Role();
            $adminRole->id = (string) Str::uuid();
            $adminRole->name = 'admin';
            $adminRole->guard_name = 'web';
            $adminRole->save();
        }

        $userRole = Role::where('name', 'user')->where('guard_name', 'web')->first();
        if (! $userRole) {
            $userRole = new Role();
            $userRole->id = (string) Str::uuid();
            $userRole->name = 'user';
            $userRole->guard_name = 'web';
            $userRole->save();
        }
    }

    public function test_admin_can_export_audits_as_csv(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $admin->assignRole('admin');

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
                'tags' => '["auth","outcome:success"]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-log.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('audit_logs.csv', $response->headers->get('content-disposition'));

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

        $admin->assignRole('admin');

        DB::table('audits')->insert([
            [
                'user_type' => User::class,
                'user_id' => $account->id,
                'event' => 'login_failure',
                'auditable_type' => null,
                'auditable_id' => null,
                'old_values' => null,
                'new_values' => '{"reason":"invalid_credentials"}',
                'url' => '/login',
                'ip_address' => '127.0.0.1',
                'user_agent' => 'PHPUnit',
                'tags' => '["auth","outcome:failure"]',
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
                'tags' => '["auth","outcome:success"]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.audit-log.export', ['event' => 'logout']));

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

        $user->assignRole('user');

        $this->actingAs($user)
            ->get(route('admin.audit-log.export'))
            ->assertForbidden();
    }

    public function test_export_writes_audit_log_exported_event(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'Admin',
            'status' => 'ACTIVE',
        ]);

        $admin = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $admin->assignRole('admin');

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
                'tags' => '["auth","outcome:success"]',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($admin)
            ->get(route('admin.audit-log.export'))
            ->assertOk();

        $this->assertDatabaseHas('audits', [
            'event' => 'audit_log_exported',
            'user_id' => $account->id,
        ]);
    }
}

<?php

namespace Tests\Feature\Admin;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAuditLogIndexTest extends TestCase
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

    public function test_admin_can_list_audits(): void
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
            ->getJson(route('admin.audit-log.index'));

        $response->assertOk();
        $response->assertJsonFragment([
            'event' => 'login_success',
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
            ->getJson(route('admin.audit-log.index', ['event' => 'logout']));

        $response->assertOk();
        $response->assertJsonFragment(['event' => 'logout']);
        $response->assertJsonMissing(['event' => 'login_failure']);
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
            ->getJson(route('admin.audit-log.index', ['tag' => 'outcome:success']));

        $response->assertOk();
        $response->assertJsonFragment(['event' => 'logout']);
        $response->assertJsonMissing(['event' => 'login_failure']);
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

        $user->assignRole('user');

        $this->actingAs($user)
            ->getJson(route('admin.audit-log.index'))
            ->assertForbidden();
    }
}

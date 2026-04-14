<?php

namespace Tests\Feature\Reminders;

use App\Models\Account;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_command_deletes_notifications_older_than_default_retention_window(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Notification::query()->insert([
            [
                'account_id' => $account->id,
                'type' => 'reminder',
                'message' => 'Old notification',
                'link' => '/user/form-select',
                'status' => 'read',
                'created_at' => now()->subDays(31),
                'updated_at' => now()->subDays(31),
            ],
            [
                'account_id' => $account->id,
                'type' => 'reminder',
                'message' => 'Recent notification',
                'link' => '/user/form-select',
                'status' => 'unread',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
        ]);

        $this->artisan('notifications:cleanup')
            ->expectsOutput('Deleted 1 notifications older than 30 days.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', [
            'account_id' => $account->id,
            'message' => 'Old notification',
        ]);

        $this->assertDatabaseHas('notifications', [
            'account_id' => $account->id,
            'message' => 'Recent notification',
        ]);
    }

    public function test_cleanup_command_respects_custom_days_option(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        Notification::query()->insert([
            [
                'account_id' => $account->id,
                'type' => 'alert',
                'message' => 'Eight day old notification',
                'link' => '/user/dashboard',
                'status' => 'read',
                'created_at' => now()->subDays(8),
                'updated_at' => now()->subDays(8),
            ],
            [
                'account_id' => $account->id,
                'type' => 'alert',
                'message' => 'Three day old notification',
                'link' => '/user/dashboard',
                'status' => 'unread',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ]);

        $this->artisan('notifications:cleanup --days=7')
            ->expectsOutput('Deleted 1 notifications older than 7 days.')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', [
            'account_id' => $account->id,
            'message' => 'Eight day old notification',
        ]);

        $this->assertDatabaseHas('notifications', [
            'account_id' => $account->id,
            'message' => 'Three day old notification',
        ]);
    }
}
<?php

namespace Tests\Feature\Notifications;

use App\Jobs\ProcessScheduledReminders;
use App\Models\Account;
use App\Models\ReminderSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderNotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_scheduled_reminders_creates_daily_notification(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        User::factory()->create([
            'account_id' => $account->id,
        ]);

        ReminderSetting::create([
            'account_id' => $account->id,
            'frequency' => 'daily',
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        app(ProcessScheduledReminders::class)->handle();

        $this->assertDatabaseHas('notifications', [
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Please complete your required health form or data entry for today.',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);
    }

    public function test_process_scheduled_reminders_does_not_duplicate_daily_notification(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        User::factory()->create([
            'account_id' => $account->id,
        ]);

        ReminderSetting::create([
            'account_id' => $account->id,
            'frequency' => 'daily',
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        app(ProcessScheduledReminders::class)->handle();
        app(ProcessScheduledReminders::class)->handle();

        // The dedupe_key should prevent a second notification in the same day
        $this->assertSame(
            1,
            \App\Models\Notification::query()
                ->where('account_id', $account->id)
                ->where('type', 'reminder')
                ->count()
        );
    }
}
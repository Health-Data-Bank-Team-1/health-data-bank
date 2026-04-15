<?php

namespace Tests\Feature\Reminders;

use App\Jobs\ProcessScheduledReminders;
use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\Notification;
use App\Models\ReminderSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use App\Models\FormTemplate;

class ProcessScheduledRemindersTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_daily_reminder_notification_when_due_and_no_submission_exists(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
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

    public function test_does_not_create_daily_reminder_if_user_submitted_today(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        ReminderSetting::create([
            'account_id' => $account->id,
            'frequency' => 'daily',
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        $template = FormTemplate::factory()->create();

        FormSubmission::create([
            'account_id' => $account->id,
            'form_template_id' => $template->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        app(ProcessScheduledReminders::class)->handle();

        $this->assertDatabaseMissing('notifications', [
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Please complete your required health form or data entry for today.',
        ]);
    }

    public function test_does_not_create_duplicate_daily_reminder_for_same_day(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        ReminderSetting::create([
            'account_id' => $account->id,
            'frequency' => 'daily',
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Please complete your required health form or data entry for today.',
            'link' => '/user/form-select',
            'status' => 'unread',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        app(ProcessScheduledReminders::class)->handle();

        $this->assertEquals(
            1,
            Notification::where('account_id', $account->id)
                ->where('type', 'reminder')
                ->count()
        );
    }

    public function test_opening_notification_marks_it_as_read_and_shows_modal_when_no_link_exists(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $notification = Notification::create([
            'account_id' => $account->id,
            'type' => 'system',
            'message' => 'Test notification',
            'link' => null,
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Notifications::class)
            ->call('open', $notification)
            ->assertSet('showModal', true);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }
}

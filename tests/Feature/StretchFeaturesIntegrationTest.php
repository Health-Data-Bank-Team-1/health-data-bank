<?php

namespace Tests\Feature;

use App\Jobs\ProcessScheduledReminders;
use App\Models\Account;
use App\Models\FormSubmission;
use App\Models\HealthEntry;
use App\Models\Notification;
use App\Models\ReminderSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StretchFeaturesIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_reminder_flows_into_notification_list_and_can_be_opened(): void
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

        $notification = Notification::query()
            ->where('account_id', $account->id)
            ->where('type', 'reminder')
            ->first();

        $this->assertNotNull($notification);

        $this->assertDatabaseHas('notifications', [
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Please complete your required health form or data entry for today.',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Please complete your required health form or data entry for today.');

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect('/user/form-select');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }

    public function test_todo_inactivity_reminder_creates_notification_for_inactive_user(): void
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
            'frequency' => 'todo',
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        app(ProcessScheduledReminders::class)->handle();

        $this->assertDatabaseHas('notifications', [
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'You have not submitted health data recently. Please complete your required form or data entry.',
            'status' => 'unread',
            'link' => '/user/todo',
        ]);
    }

    public function test_summary_endpoint_returns_generated_suggestions_for_user_data(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 90],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-02 10:00:00'),
            'encrypted_values' => ['hr' => 92],
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-03 10:00:00'),
            'encrypted_values' => ['hr' => 94],
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-04-01&to=2026-04-07&keys=hr'
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'averages',
            'counts',
            'suggestions',
        ]);

        $suggestions = $response->json('suggestions');
        $types = array_column($suggestions, 'type');

        $this->assertIsArray($suggestions);
        $this->assertNotEmpty($suggestions);
        $this->assertContains('high_value', $types);
    }

    public function test_summary_endpoint_returns_insufficient_data_suggestion_when_data_is_too_small(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        HealthEntry::factory()->create([
            'account_id' => $account->id,
            'timestamp' => Carbon::parse('2026-04-01 10:00:00'),
            'encrypted_values' => ['hr' => 72],
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson(
            '/api/me/summary?from=2026-04-01&to=2026-04-07&keys=hr'
        );

        $response->assertStatus(200);

        $suggestions = $response->json('suggestions');
        $types = array_column($suggestions, 'type');

        $this->assertContains('insufficient_data', $types);
    }
}
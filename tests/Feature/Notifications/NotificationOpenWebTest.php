<?php

namespace Tests\Feature\Notifications;

use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationOpenWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_opening_notification_marks_read_and_redirects(): void
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
            'type' => 'reminder',
            'dedupe_key' => 'reminder:daily:'.now()->toDateString(),
            'message' => 'Test reminder',
            'link' => '/user/form-select',
            'status' => 'unread',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect('/user/form-select');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }
}
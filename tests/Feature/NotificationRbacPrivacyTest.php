<?php

namespace Tests\Feature\Notifications;

use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationRbacPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_notification_index(): void
    {
        $this->get(route('notifications.index'))
            ->assertRedirect();
    }

    public function test_guest_cannot_open_notification(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $notification = Notification::factory()->create([
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Reminder for owner only',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $this->get(route('notifications.open', $notification))
            ->assertRedirect();
    }

    public function test_user_can_view_only_their_own_notifications(): void
    {
        $accountA = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $userA = User::factory()->create([
            'account_id' => $accountA->id,
        ]);

        $accountB = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        User::factory()->create([
            'account_id' => $accountB->id,
        ]);

        Notification::factory()->create([
            'account_id' => $accountA->id,
            'type' => 'reminder',
            'message' => 'A private notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        Notification::factory()->create([
            'account_id' => $accountB->id,
            'type' => 'reminder',
            'message' => 'B private notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $this->actingAs($userA)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('A private notification')
            ->assertDontSee('B private notification');
    }

    public function test_user_cannot_open_another_users_notification(): void
    {
        $ownerAccount = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        User::factory()->create([
            'account_id' => $ownerAccount->id,
        ]);

        $otherAccount = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $otherUser = User::factory()->create([
            'account_id' => $otherAccount->id,
        ]);

        $notification = Notification::factory()->create([
            'account_id' => $ownerAccount->id,
            'type' => 'reminder',
            'message' => 'Owner-only notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $response = $this->actingAs($otherUser)
            ->get(route('notifications.open', $notification));

        $response->assertStatus(403);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'unread',
        ]);
    }

    public function test_owner_can_open_their_notification_and_it_is_marked_read(): void
    {
        $account = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $user = User::factory()->create([
            'account_id' => $account->id,
        ]);

        $notification = Notification::factory()->create([
            'account_id' => $account->id,
            'type' => 'reminder',
            'message' => 'Owner notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.open', $notification))
            ->assertRedirect('/user/form-select');

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }

    public function test_opening_one_notification_does_not_change_someone_elses_notification(): void
    {
        $accountA = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        $userA = User::factory()->create([
            'account_id' => $accountA->id,
        ]);

        $accountB = Account::factory()->create([
            'account_type' => 'User',
            'status' => 'ACTIVE',
        ]);

        User::factory()->create([
            'account_id' => $accountB->id,
        ]);

        $notificationA = Notification::factory()->create([
            'account_id' => $accountA->id,
            'type' => 'reminder',
            'message' => 'A notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $notificationB = Notification::factory()->create([
            'account_id' => $accountB->id,
            'type' => 'reminder',
            'message' => 'B notification',
            'status' => 'unread',
            'link' => '/user/form-select',
        ]);

        $this->actingAs($userA)
            ->get(route('notifications.open', $notificationA))
            ->assertRedirect('/user/form-select');

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationA->id,
            'status' => 'read',
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notificationB->id,
            'status' => 'unread',
        ]);
    }
}
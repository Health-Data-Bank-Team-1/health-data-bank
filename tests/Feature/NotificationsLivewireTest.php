<?php

namespace Tests\Feature;

use App\Livewire\Notifications;
use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsLivewireTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithAccount(): User
    {
        $account = Account::factory()->create();
        $user = User::factory()->create(['account_id' => $account->id]);

        return $user;
    }

    public function test_guest_cannot_access_notifications_page(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_notifications_page(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
    }

    public function test_notifications_page_shows_user_notifications(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Test notification message',
            'type' => 'alert',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertSee('Test notification message');
        $response->assertSee('Alert');
        $response->assertSee('Unread');
    }

    public function test_notifications_page_shows_empty_state(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertSee('No notifications found.');
    }

    public function test_notifications_page_only_shows_users_own_notifications(): void
    {
        $user = $this->createUserWithAccount();
        $otherAccount = Account::factory()->create();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'My notification',
        ]);
        Notification::factory()->create([
            'account_id' => $otherAccount->id,
            'message' => 'Someone else notification',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertSee('My notification');
        $response->assertDontSee('Someone else notification');
    }

    public function test_view_renders_notification_details(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Reminder to log data',
            'type' => 'reminder',
            'status' => 'read',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertSee('Reminder to log data');
        $response->assertSee('Reminder');
        $response->assertSee('Read');
    }

    public function test_open_marks_notification_as_read(): void
    {
        $user = $this->createUserWithAccount();

        $notification = Notification::factory()->create([
            'account_id' => $user->account_id,
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->call('open', $notification)
            ->assertSet('showModal', true)
            ->assertSet('selectedNotification.id', $notification->id);

        $this->assertEquals('read', $notification->fresh()->status);
    }

    public function test_open_sets_modal_properties(): void
    {
        $user = $this->createUserWithAccount();

        $notification = Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Important alert',
            'type' => 'alert',
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->call('open', $notification)
            ->assertSet('showModal', true)
            ->assertSet('selectedNotification.message', 'Important alert');
    }

    public function test_open_aborts_for_notification_belonging_to_other_account(): void
    {
        $user = $this->createUserWithAccount();
        $otherAccount = Account::factory()->create();

        $notification = Notification::factory()->create([
            'account_id' => $otherAccount->id,
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->call('open', $notification)
            ->assertStatus(403);
    }

    public function test_open_does_not_change_status_for_unauthorized_notification(): void
    {
        $user = $this->createUserWithAccount();
        $otherAccount = Account::factory()->create();

        $notification = Notification::factory()->create([
            'account_id' => $otherAccount->id,
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->call('open', $notification);

        $this->assertEquals('unread', $notification->fresh()->status);
    }

    public function test_closing_modal_resets_show_modal(): void
    {
        $user = $this->createUserWithAccount();

        $notification = Notification::factory()->create([
            'account_id' => $user->account_id,
            'status' => 'unread',
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->call('open', $notification)
            ->assertSet('showModal', true)
            ->set('showModal', false)
            ->assertSet('showModal', false);
    }

    public function test_view_contains_wire_click_buttons(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Clickable notification',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertSee('wire:click="open(', false);
    }

    public function test_view_paginates_notifications(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->count(20)->create([
            'account_id' => $user->account_id,
        ]);

        Livewire::actingAs($user)
            ->test(Notifications::class)
            ->assertViewHas('notifications', fn ($notifications) => $notifications->count() === 15);
    }

    public function test_view_contains_close_button(): void
    {
        $user = $this->createUserWithAccount();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $content = $response->getContent();
        $this->assertStringContainsString('Close', $content);
    }

    public function test_view_shows_unread_styling(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Unread notification',
            'status' => 'unread',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $content = $response->getContent();
        $this->assertStringContainsString('#f87171', $content);
        $this->assertStringContainsString('bg-red-100 text-red-700', $content);
    }

    public function test_view_shows_read_styling(): void
    {
        $user = $this->createUserWithAccount();

        Notification::factory()->create([
            'account_id' => $user->account_id,
            'message' => 'Read notification',
            'status' => 'read',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $content = $response->getContent();
        $this->assertStringContainsString('#4ade80', $content);
        $this->assertStringContainsString('bg-green-100 text-green-700', $content);
    }
}

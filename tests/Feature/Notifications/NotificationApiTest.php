<?php

namespace Tests\Feature\Notifications;

use App\Models\Account;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_authenticated_accounts_notifications(): void
    {
        $accountA = Account::factory()->create();
        $userA = User::factory()->create(['account_id' => $accountA->id]);

        $accountB = Account::factory()->create();
        User::factory()->create(['account_id' => $accountB->id]);

        Notification::create([
            'account_id' => $accountA->id,
            'type' => 'reminder',
            'dedupe_key' => 'x1',
            'message' => 'A1',
            'link' => null,
            'status' => 'unread',
        ]);

        Notification::create([
            'account_id' => $accountB->id,
            'type' => 'reminder',
            'dedupe_key' => 'x2',
            'message' => 'B1',
            'link' => null,
            'status' => 'unread',
        ]);

        Sanctum::actingAs($userA);

        $res = $this->getJson('/api/notifications');
        $res->assertOk();

        $res->assertJsonMissing(['message' => 'B1']);
        $res->assertJsonFragment(['message' => 'A1']);
    }

    public function test_index_can_filter_unread(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create(['account_id' => $account->id]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'test',
            'dedupe_key' => 'u1',
            'message' => 'Unread',
            'link' => null,
            'status' => 'unread',
        ]);

        Notification::create([
            'account_id' => $account->id,
            'type' => 'test',
            'dedupe_key' => 'r1',
            'message' => 'Read',
            'link' => null,
            'status' => 'read',
        ]);

        Sanctum::actingAs($user);

        $res = $this->getJson('/api/notifications?status=unread');
        $res->assertOk();
        $res->assertJsonFragment(['message' => 'Unread']);
        $res->assertJsonMissing(['message' => 'Read']);
    }

    public function test_can_mark_single_notification_read(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create(['account_id' => $account->id]);

        $notification = Notification::create([
            'account_id' => $account->id,
            'type' => 'test',
            'dedupe_key' => 'z1',
            'message' => 'Hello',
            'link' => null,
            'status' => 'unread',
        ]);

        Sanctum::actingAs($user);

        $res = $this->postJson("/api/notifications/{$notification->id}/read");
        $res->assertOk();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'status' => 'read',
        ]);
    }

    public function test_cannot_access_other_accounts_notification(): void
    {
        $accountA = Account::factory()->create();
        $userA = User::factory()->create(['account_id' => $accountA->id]);

        $accountB = Account::factory()->create();

        $notificationB = Notification::create([
            'account_id' => $accountB->id,
            'type' => 'test',
            'dedupe_key' => 'b1',
            'message' => 'Private',
            'link' => null,
            'status' => 'unread',
        ]);

        Sanctum::actingAs($userA);

        $this->getJson("/api/notifications/{$notificationB->id}")
            ->assertStatus(403);
    }

    public function test_mark_all_read_marks_only_this_accounts_notifications(): void
    {
        $accountA = Account::factory()->create();
        $userA = User::factory()->create(['account_id' => $accountA->id]);

        $accountB = Account::factory()->create();

        $a1 = Notification::create([
            'account_id' => $accountA->id,
            'type' => 'test',
            'dedupe_key' => 'a1',
            'message' => 'A1',
            'link' => null,
            'status' => 'unread',
        ]);

        $b1 = Notification::create([
            'account_id' => $accountB->id,
            'type' => 'test',
            'dedupe_key' => 'b1',
            'message' => 'B1',
            'link' => null,
            'status' => 'unread',
        ]);

        Sanctum::actingAs($userA);

        $this->postJson('/api/notifications/read-all')
            ->assertOk()
            ->assertJsonFragment(['ok' => true]);

        $this->assertDatabaseHas('notifications', [
            'id' => $a1->id,
            'status' => 'read',
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $b1->id,
            'status' => 'unread',
        ]);
    }
}
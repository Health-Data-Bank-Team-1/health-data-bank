<?php

namespace Tests\Feature\Notifications;

use App\Models\Account;
use App\Models\Notification;
use App\Notifications\AppNotification;
use App\Services\Notifications\NotificationDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationDeliveryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_deliver_to_account_creates_unread_notification(): void
    {
        $account = Account::factory()->create();

        /** @var NotificationDeliveryService $service */
        $service = app(NotificationDeliveryService::class);

        $payload = new class implements AppNotification {
            public function type(): string { return 'test'; }
            public function message(): string { return 'Hello'; }
            public function link(): ?string { return '/x'; }
            public function dedupeKey(): ?string { return 'test:hello'; }
        };

        $created = $service->deliverToAccount($account->id, $payload);

        $this->assertNotNull($created);

        $this->assertDatabaseHas('notifications', [
            'id' => $created->id,
            'account_id' => $account->id,
            'type' => 'test',
            'dedupe_key' => 'test:hello',
            'message' => 'Hello',
            'link' => '/x',
            'status' => 'unread',
        ]);
    }

    public function test_deliver_to_account_dedupes_by_account_id_and_dedupe_key(): void
    {
        $account = Account::factory()->create();

        /** @var NotificationDeliveryService $service */
        $service = app(NotificationDeliveryService::class);

        $payload = new class implements AppNotification {
            public function type(): string { return 'test'; }
            public function message(): string { return 'Hello'; }
            public function link(): ?string { return null; }
            public function dedupeKey(): ?string { return 'test:hello'; }
        };

        $first = $service->deliverToAccount($account->id, $payload);
        $second = $service->deliverToAccount($account->id, $payload);

        $this->assertNotNull($first);
        $this->assertNull($second);

        $this->assertSame(
            1,
            Notification::query()
                ->where('account_id', $account->id)
                ->where('dedupe_key', 'test:hello')
                ->count()
        );
    }
}
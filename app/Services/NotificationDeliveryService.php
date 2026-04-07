<?php
// app/Services/Notifications/NotificationDeliveryService.php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Notifications\AppNotification;
use Illuminate\Database\QueryException;

class NotificationDeliveryService
{
    /**
     * Deliver in-app notification to an account.
     *
     * - Stores in DB (notifications table)
     * - Uses dedupe_key if provided (backed by UNIQUE(account_id, dedupe_key))
     *
     * Returns the Notification if created, or null if it was deduped.
     */
    public function deliverToAccount(string $accountId, AppNotification $payload): ?Notification
    {
        try {
            return Notification::create([
                'account_id' => $accountId,
                'type' => $payload->type(),
                'dedupe_key' => $payload->dedupeKey(),
                'message' => $payload->message(),
                'link' => $payload->link(),
                'status' => 'unread',
            ]);
        } catch (QueryException $e) {
            // If the UNIQUE(account_id, dedupe_key) constraint is hit, treat as deduped.
            // SQLSTATE 23000 is common for integrity constraint violations in MySQL.
            if (($e->getCode() === '23000') && $payload->dedupeKey() !== null) {
                return null;
            }

            throw $e;
        }
    }
}
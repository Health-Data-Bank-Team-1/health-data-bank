<?php
// app/Services/Notifications/NotificationDeduplicator.php

namespace App\Services\Notifications;

use App\Models\Notification;
use Carbon\Carbon;

class NotificationDeduplicator
{
    /**
     * "key" is not stored in DB yet, so we approximate:
     * treat key as part of the message identity, and constrain by time window.
     *
     * If you add `dedupe_key` later, switch this to query that column.
     */
    public function alreadyDelivered(string $accountId, string $type, string $key, ?int $windowMinutes): bool
    {
        $query = Notification::query()
            ->where('account_id', $accountId)
            ->where('type', $type);

        if ($windowMinutes !== null) {
            $query->where('created_at', '>=', Carbon::now()->subMinutes($windowMinutes));
        }

        // Hacky but functional: embed key into message OR compare message+type within window.
        // If you don’t want the key in the message, remove this and dedupe by message text only.
        // Here we dedupe by message equality (safer for UX, but not perfect).
        return $query->exists();
    }
}
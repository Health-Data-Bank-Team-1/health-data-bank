<?php

use App\Services\Notifications\NotificationDeliveryService;
use App\Notifications\ReminderDueNotification;

// inside handle(), where you currently create Notification::create(...)
if (! $isComplete) {
    $delivery = app(NotificationDeliveryService::class);

    $appNotification = new ReminderDueNotification(
        frequency: $frequency,
        accountId: $accountId,
        now: $now
    );

    $delivery->deliverToAccount($accountId, $appNotification);

    $reminder->last_sent_at = $now;
}
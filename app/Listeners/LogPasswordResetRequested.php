<?php

namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Services\AuditLogger;

class LogPasswordResetRequested
{
    public function handle(NotificationSent $event): void
    {
        if (!($event->notification instanceof ResetPassword)) {
            return;
        }

        AuditLogger::log(
            'password_reset_requested',
            ['security', 'auth', 'outcome:success'],
            null,
            [],
            [
                'channel' => $event->channel,
            ]
        );
    }
}

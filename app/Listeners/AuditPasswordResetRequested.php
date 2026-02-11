<?php
namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Services\AuditLogger;

class AuditPasswordResetRequested
{
    public function handle(NotificationSent $event): void
    {
        if (!($event->notification instanceof ResetPassword)) {
            return;
        }

        // $event->notifiable is usually the User model
        $user = $event->notifiable;


        AuditLogger::log(
            'password_reset_requested',
            ['security', 'auth'],
            $user,
            [],
            [
                // Avoid logging reset token or full URL
                'channel' => $event->channel,
            ]
        );
    }
}

<?php
namespace App\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LogPasswordResetRequested {
    public function handle(NotificationSent $event): void
    {
        if (!($event->notification instanceof ResetPassword)) {
            return;
        }

        $actorId = null;

        // $event->notifiable is usually the User model receiving the reset notification
        if ($event->notifiable && !empty($event->notifiable->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $event->notifiable->email)
                ->value('id');
        }

        AuditLogger::log(
            'password_reset_requested',
            'success',
            'notification_sent',
            'account',
            $actorId, // target_id
            ['channel' => $event->channel],
            $actorId  // actor override
        );
    }
}

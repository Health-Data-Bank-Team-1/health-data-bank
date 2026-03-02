<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditLogger;
<<<<<<< audit-password-and-profile-events
use Illuminate\Support\Facades\DB;
=======
>>>>>>> main

class LogLoginSuccess
{
    public function handle(Login $event): void
    {
<<<<<<< audit-password-and-profile-events
        $actorId = null;

        if ($event->user && !empty($event->user->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $event->user->email)
                ->value('id');
        }

        AuditLogger::log(
            'login_success',
            'success',
            null,
            'account',
            $actorId,                 // target_id
            ['guard' => $event->guard],
            $actorId                  // actor override
=======
        // Attach audit to the authenticated user (Option A)
        AuditLogger::log(
            'login_success',
            ['auth', 'outcome:success'],
            $event->user,
            [],
            []
>>>>>>> main
        );
    }
}

<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogger;
<<<<<<< audit-password-and-profile-events
use Illuminate\Support\Facades\DB;
=======
>>>>>>> main

class LogLoginFailure
{
    public function handle(Failed $event): void
    {
<<<<<<< audit-password-and-profile-events
        $actorId = null;

        // Sometimes $event->user is null on failed attempts
        if ($event->user && !empty($event->user->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $event->user->email)
                ->value('id');
        }

        AuditLogger::log(
            'login_failure',
            'failure',
            'invalid_credentials',
            'account',
            $actorId,                 // target_id when available
            ['guard' => $event->guard],
            $actorId                  // actor override
        );
=======
        // $event->user can be null if email not found
        $auditable = $event->user ?? null;

        // If no user exists, you can either:
        // 1) Skip auditable logging, or
        // 2) Attach to a System audit anchor (later)
        // For Sprint 2: only log if user exists
        if ($auditable) {
            AuditLogger::log(
                'login_failure',
                ['auth', 'outcome:failure'],
                $auditable,
                [],
                ['reason' => 'invalid_credentials']
            );
        }
>>>>>>> main
    }
}

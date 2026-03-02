<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LogLoginFailure
{
    public function handle(Failed $event): void
    {
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
    }
}

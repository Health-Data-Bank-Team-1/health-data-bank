<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LogLoginSuccess
{
    public function handle(Login $event): void
    {
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
        );
    }
}

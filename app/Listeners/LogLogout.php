<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LogLogout
{
    public function handle(Logout $event): void
    {
        $actorId = null;

        if ($event->user && !empty($event->user->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $event->user->email)
                ->value('id');
        }
        AuditLogger::log(
            'logout',
            'success',
            null,
            'account',
            $actorId,                 // target id
            ['guard' => $event->guard],
            $actorId                  // actor override
        );
    }
}

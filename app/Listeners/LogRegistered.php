<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

class LogRegistered
{
    public function handle(Registered $event): void
    {
        $actorId = null;

        if ($event->user && !empty($event->user->email)) {
            $actorId = DB::table('accounts')
                ->where('email', $event->user->email)
                ->value('id');
        }
        AuditLogger::log(
            'register_success',
            'success',
            null,
            'account',
            $actorId, //  target_id
            ['source' => 'fortify_registered'],
            $actorId  //  actor override
        );
    }
}

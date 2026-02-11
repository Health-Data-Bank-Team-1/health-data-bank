<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\AuditLogger;

class AuditLogout {
    public function handle(Logout $event): void
    {
        AuditLogger::log(
            'logout',
            ['auth', 'outcome:success'],
            $event->user,
            [],
            []
        );
    }
}

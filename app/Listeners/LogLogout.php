<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Services\AuditLogger;

class LogLogout
{
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

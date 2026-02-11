<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditLogger;

class LogLoginSuccess
{
    public function handle(Login $event): void
    {
        // Attach audit to the authenticated user (Option A)
        AuditLogger::log(
            'login_success',
            ['auth', 'outcome:success'],
            $event->user,
            [],
            []
        );
    }
}

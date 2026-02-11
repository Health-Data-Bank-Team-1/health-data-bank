<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditLogger;

class AuditLoginSuccess {
    public function handle(Login $event): void
    {
        // Attach audit to the authenticated user
        AuditLogger::log(
            'login_success',
            ['auth', 'outcome:success'],
            $event->user,
            [],
            []
        );
    }
}

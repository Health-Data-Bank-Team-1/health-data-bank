<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\AuditLogger;

class LogLoginSuccess
{
    public function handle(Login $event): void
    {
        AuditLogger::log(
            'login_success',
            ['auth', 'outcome:success'],
            null,
            [],
            []
        );
    }
}

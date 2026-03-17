<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogger;

class LogLoginFailure
{
    public function handle(Failed $event): void
    {
        AuditLogger::log(
            'login_failure',
            ['auth', 'outcome:failure'],
            $event->user,
            [],
            ['reason' => 'invalid_credentials']
        );
    }
}

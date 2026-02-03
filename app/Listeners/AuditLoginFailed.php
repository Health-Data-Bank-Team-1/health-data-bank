<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogger;

class LogLoginFailure
{
    public function handle(Failed $event): void
    {
        // $event->user can be null if email not found
        $auditable = $event->user ?? null;

        // If no user exists, you can either:
        // 1) Skip auditable logging, or
        // 2) Attach to a System audit anchor (later)
        // For Sprint 2: only log if user exists
        if ($auditable) {
            AuditLogger::log(
                'login_failure',
                ['auth', 'outcome:failure'],
                $auditable,
                [],
                ['reason' => 'invalid_credentials']
            );
        }
    }
}

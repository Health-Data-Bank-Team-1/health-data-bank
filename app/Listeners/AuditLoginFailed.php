<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Services\AuditLogger;

class AuditLoginFailed{
    public function handle(Failed $event): void{
        $auditable = $event->user;

        AuditLogger::log(
            'login_failure',
            ['auth', 'outcome:failure'],
            $auditable,
            [],
            [
                'reason' => 'invalid_credentials',
                'email_attempted' => $event->credentials['email'] ?? null,
            ]
        );
    }
}


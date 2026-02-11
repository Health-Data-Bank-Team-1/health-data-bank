<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Services\AuditLogger;

class AuditRegistered
{
    public function handle(Registered $event): void
    {
        AuditLogger::log(
            'register_success',
            ['auth', 'outcome:success'],
            $event->user,
            [],
            []
        );
    }
}

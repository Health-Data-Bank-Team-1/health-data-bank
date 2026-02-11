<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

use App\Listeners\AuditPasswordResetRequested;
use App\Listeners\AuditLoginSuccess;
use App\Listeners\AuditLoginFailed;
use App\Listeners\AuditLogout;
use App\Listeners\AuditRegistered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            AuditLoginSuccess::class,
        ],

        Failed::class => [
            AuditLoginFailed::class,
        ],

        Logout::class => [
            AuditLogout::class,
        ],

        Registered::class => [
            AuditRegistered::class,
        ],

        NotificationSent::class => [
            AuditPasswordResetRequested::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}

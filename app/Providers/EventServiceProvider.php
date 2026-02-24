<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Notifications\Events\NotificationSent;

use App\Listeners\LogPasswordResetRequested;
use App\Listeners\LogLoginSuccess;
use App\Listeners\LogLoginFailure;
use App\Listeners\LogLogout;
use App\Listeners\LogRegistered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Login::class => [
            LogLoginSuccess::class,
        ],

        Failed::class => [
            LogLoginFailure::class,
        ],

        Logout::class => [
            LogLogout::class,
        ],

        Registered::class => [
            LogRegistered::class,
        ],

        NotificationSent::class => [
            LogPasswordResetRequested::class,
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

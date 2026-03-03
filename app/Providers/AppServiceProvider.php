<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HealthDataEncryptionService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register encryption service as singleton
        $this->app->singleton(HealthDataEncryptionService::class, function ($app) {
            return new HealthDataEncryptionService();
        });
    }

    public function boot(): void
    {
        // Boot logic here
    }
}
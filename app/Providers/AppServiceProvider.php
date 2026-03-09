<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\FormTemplate;
use App\Observers\FormTemplateObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FormTemplate::observe(FormTemplateObserver::class);
    }
}

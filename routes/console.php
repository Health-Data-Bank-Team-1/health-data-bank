<?php

use App\Jobs\ProcessScheduledReminders;
use App\Models\Notification;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:cleanup {--days=30}', function () {
    $days = max((int) $this->option('days'), 1);

    $deleted = Notification::query()
        ->where('created_at', '<', now()->subDays($days))
        ->delete();

    $this->info("Deleted {$deleted} notifications older than {$days} days.");
})->purpose('Delete old notifications based on retention age');

Schedule::call(function () {
    app(ProcessScheduledReminders::class)->handle();
})->hourly();

Schedule::command('notifications:cleanup')->daily();
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Daily compressed database backup at 2 AM
        $schedule->command('backup:database --compress')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onSuccess(function () {
                Log::info('Daily database backup completed successfully');
            })
            ->onFailure(function () {
                Log::error('Daily database backup failed');
            });

        // Weekly compressed backup every Sunday at 3 AM
        $schedule->command('backup:database --compress')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping();

        // Monthly compressed backup plus cleanup of old backups on the 1st at 4 AM
        $schedule->command('backup:database --compress --cleanup --retention=30')
            ->monthlyOn(1, '04:00')
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

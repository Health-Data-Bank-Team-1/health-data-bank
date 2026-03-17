<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Daily database backup at 2 AM
        $schedule->command('backup:database')
                 ->daily()
                 ->at('02:00')
                 ->withoutOverlapping()
                 ->onSuccess(function () {
                     // Optional: Send success notification
                     \Illuminate\Support\Facades\Log::info('Database backup completed successfully');
                 })
                 ->onFailure(function () {
                     // Optional: Send failure alert
                     \Illuminate\Support\Facades\Log::error('Database backup failed');
                 });

        // Optional: Weekly backup with compression
        $schedule->command('backup:database --compress')
                 ->weekly()
                 ->sundays()
                 ->at('03:00')
                 ->withoutOverlapping();

        // Optional: Clean up old backups monthly
        $schedule->command('backup:database --cleanup --retention=30')
                 ->monthlyOn(1, '04:00')
                 ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
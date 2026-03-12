<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup old sessions daily at 2 AM
        $schedule->command('cleanup:old-sessions --days=7 --notify')
                 ->dailyAt('02:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Weekly cleanup report on Sundays at 8 AM
        $schedule->command('cleanup:old-sessions --days=30 --dry-run --notify')
                 ->weekly()
                 ->sundays()
                 ->at('08:00')
                 ->timezone('Asia/Jakarta')
                 ->withoutOverlapping();

        // Log scheduling activity
        $schedule->call(function () {
            \App\Models\LogAktivitas::logSystem(
                'Scheduler executed successfully',
                ['timestamp' => now()->format('Y-m-d H:i:s')],
                \App\Models\LogAktivitas::LEVEL_DEBUG
            );
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

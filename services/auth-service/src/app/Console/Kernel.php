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
        // Очистка истекших refresh токенов каждые 6 часов
        $schedule->command('tokens:cleanup-refresh')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Очистка истекших email токенов каждые 6 часов
        $schedule->command('tokens:cleanup-email')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();
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

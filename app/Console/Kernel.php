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
        // Synchronisation automatique des bases de données toutes les heures
        $schedule->command('db:sync:auto --from=pgsql --to=neon')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();

        // Autres tâches planifiées existantes...
        $schedule->command('jobs:unblock-expired-accounts')->daily();
        $schedule->command('jobs:archive-expired-blocked-accounts')->weekly();
        $schedule->command('jobs:unarchive-expired-blocked-accounts')->weekly();
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

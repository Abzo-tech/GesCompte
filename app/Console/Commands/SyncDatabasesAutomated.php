<?php

namespace App\Console\Commands;

use App\Jobs\SyncDatabasesJob;
use Illuminate\Console\Command;

class SyncDatabasesAutomated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync:auto {--from=pgsql : Base de données source} {--to=neon : Base de données destination} {--schedule : Programmer la synchronisation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronisation automatique des bases de données via job queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $schedule = $this->option('schedule');

        if ($schedule) {
            $this->scheduleSync($from, $to);
        } else {
            $this->runSync($from, $to);
        }
    }

    private function runSync($from, $to)
    {
        $this->info("Lancement de la synchronisation automatique: {$from} -> {$to}");

        // Dispatch le job
        SyncDatabasesJob::dispatch($from, $to);

        $this->info('Job de synchronisation envoyé à la queue !');
        $this->info('Utilisez `php artisan queue:work` pour traiter le job.');
    }

    private function scheduleSync($from, $to)
    {
        $this->info("Programmation de la synchronisation: {$from} -> {$to}");

        // Programmer le job pour dans 1 minute (pour test)
        SyncDatabasesJob::dispatch($from, $to)->delay(now()->addMinute());

        $this->info('Job programmé pour dans 1 minute !');
    }
}
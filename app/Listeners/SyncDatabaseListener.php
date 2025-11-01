<?php

namespace App\Listeners;

use App\Events\DatabaseChanged;
use App\Jobs\SyncDatabasesJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncDatabaseListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $syncTables = ['clients', 'comptes', 'transactions'];

    /**
     * Handle the event.
     */
    public function handle(DatabaseChanged $event): void
    {
        // Vérifier si la table doit être synchronisée
        if (!in_array($event->table, $this->syncTables)) {
            return;
        }

        // Utiliser un cache pour éviter les synchronisations trop fréquentes
        $cacheKey = "sync_{$event->table}";
        $lastSync = Cache::get($cacheKey);

        // Si une synchronisation a eu lieu il y a moins de 5 secondes, ignorer
        if ($lastSync && now()->diffInSeconds($lastSync) < 5) {
            Log::info("Synchronisation ignorée pour {$event->table} (trop récente)");
            return;
        }

        Log::info("Changement détecté dans {$event->table}, déclenchement de la synchronisation");

        // Mettre à jour le cache
        Cache::put($cacheKey, now(), 300); // 5 minutes

        // Dispatcher le job de synchronisation
        SyncDatabasesJob::dispatch('pgsql', 'neon', [$event->table])
            ->onQueue('sync')
            ->delay(now()->addSeconds(2)); // Délai de 2 secondes pour éviter les conflits
    }
}
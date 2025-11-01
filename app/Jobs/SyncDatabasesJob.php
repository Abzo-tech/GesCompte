<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDatabasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $from;
    protected $to;
    protected $tables;

    /**
     * Create a new job instance.
     */
    public function __construct($from = 'pgsql', $to = 'neon', $tables = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->tables = $tables ?? ['clients', 'comptes', 'transactions'];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Début de la synchronisation automatique: {$this->from} -> {$this->to}");

        $totalSynced = 0;

        foreach ($this->tables as $table) {
            try {
                $synced = $this->syncTable($table, $this->from, $this->to);
                $totalSynced += $synced;
                Log::info("Table {$table}: {$synced} enregistrements synchronisés");
            } catch (\Exception $e) {
                Log::error("Erreur lors de la synchronisation de {$table}: " . $e->getMessage());
            }
        }

        Log::info("Synchronisation terminée: {$totalSynced} enregistrements au total");
    }

    private function syncTable($table, $from, $to)
    {
        // Vider la table destination
        DB::connection($to)->table($table)->truncate();

        // Récupérer les données de la source
        $data = DB::connection($from)->table($table)->get();

        if ($data->isEmpty()) {
            return 0;
        }

        // Insérer les données dans la destination par lots
        $chunks = $data->chunk(500); // Traiter par lots de 500
        $totalInserted = 0;

        foreach ($chunks as $chunk) {
            $records = $chunk->map(function ($item) {
                return (array) $item;
            })->toArray();

            DB::connection($to)->table($table)->insert($records);
            $totalInserted += count($records);
        }

        return $totalInserted;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de synchronisation échoué: ' . $exception->getMessage());
    }
}
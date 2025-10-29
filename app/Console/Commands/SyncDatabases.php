<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sync {--from=pgsql : Base de données source} {--to=neon : Base de données destination}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les données entre deux bases de données';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $from = $this->option('from');
        $to = $this->option('to');

        $this->info("Synchronisation des données de {$from} vers {$to}");

        // Tables à synchroniser
        $tables = ['clients', 'comptes', 'transactions'];

        foreach ($tables as $table) {
            $this->syncTable($table, $from, $to);
        }

        $this->info('Synchronisation terminée !');
    }

    private function syncTable($table, $from, $to)
    {
        $this->info("Synchronisation de la table: {$table}");

        try {
            // Vérifier si la table existe dans la destination
            $tableExists = DB::connection($to)->select("SELECT EXISTS (
                SELECT FROM information_schema.tables
                WHERE table_schema = 'public'
                AND table_name = ?
            )", [$table]);

            if (!$tableExists[0]->exists) {
                $this->warn("⚠️  Table {$table} n'existe pas dans la base {$to}, création en cours...");

                // Créer la table en copiant la structure depuis la source
                $this->createTableFromSource($table, $from, $to);
            }

            // Vider la table destination
            DB::connection($to)->table($table)->truncate();

            // Récupérer les données de la source
            $data = DB::connection($from)->table($table)->get();

            if ($data->isEmpty()) {
                $this->warn("Aucune donnée trouvée dans {$table}");
                return;
            }

            // Insérer les données dans la destination
            $chunks = $data->chunk(100); // Traiter par lots de 100

            foreach ($chunks as $chunk) {
                $records = $chunk->map(function ($item) {
                    return (array) $item;
                })->toArray();

                DB::connection($to)->table($table)->insert($records);
            }

            $this->info("✅ {$data->count()} enregistrements synchronisés pour {$table}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la synchronisation de {$table}: " . $e->getMessage());
        }
    }

    private function createTableFromSource($table, $from, $to)
    {
        // Utiliser les migrations Laravel pour créer les tables
        $this->info("📋 Utilisation des migrations Laravel pour créer la table {$table}");

        // Exécuter les migrations spécifiques à cette table
        $migrationFiles = [
            'clients' => '2025_10_27_154138_create_clients_table',
            'comptes' => '2025_10_27_172607_create_comptes_table',
            'transactions' => '2025_10_28_161558_create_transactions_table'
        ];

        if (isset($migrationFiles[$table])) {
            $this->call('migrate', [
                '--database' => $to,
                '--path' => "database/migrations/{$migrationFiles[$table]}.php",
                '--force' => true
            ]);
        } else {
            throw new \Exception("Migration non trouvée pour la table {$table}");
        }
    }
}
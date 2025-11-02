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
    protected $signature = 'db:sync {--from=pgsql : Base de données source} {--to=neon : Base de données destination} {--create-tables : Créer les tables si elles n\'existent pas}';

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
        $createTables = $this->option('create-tables');

        $this->info("Synchronisation des données de {$from} vers {$to}");

        // Tables à synchroniser
        $tables = ['clients', 'comptes', 'transactions'];

        // Si l'option --create-tables est activée, créer d'abord les tables
        if ($createTables) {
            $this->info("🏗️  Création des tables dans {$to} si nécessaire...");
            foreach ($tables as $table) {
                $this->ensureTableExists($table, $from, $to);
            }
        }

        foreach ($tables as $table) {
            $this->syncTable($table, $from, $to);
        }

        $this->info('Synchronisation terminée !');
    }

    private function syncTable($table, $from, $to)
    {
        $this->info("Synchronisation de la table: {$table}");

        try {
            // S'assurer que la table existe dans la destination
            $this->ensureTableExists($table, $from, $to);

            // Vider la table destination
            DB::connection($to)->table($table)->truncate();

            // Récupérer les données de la source
            $data = DB::connection($from)->table($table)->get();

            if ($data->isEmpty()) {
                $this->warn("Aucune donnée trouvée dans {$table}");
                return;
            }

            // Insérer les données dans la destination par enregistrement individuel
            // pour éviter les problèmes de types avec les UUIDs
            $successCount = 0;
            $errorCount = 0;

            foreach ($data as $record) {
                try {
                    DB::connection($to)->table($table)->insert((array) $record);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorMessage = $e->getMessage();
                    $errorCount++;

                    // Si c'est une erreur de type UUID/BIGINT, recréer la table
                    if (str_contains($errorMessage, 'invalid input syntax for type bigint')) {

                        $this->warn("🔄 Erreur de type détectée, recréation de la table {$table}...");

                        // Supprimer et recréer la table
                        DB::connection($to)->statement("DROP TABLE IF EXISTS \"{$table}\" CASCADE");
                        $this->createTableFromSource($table, $from, $to);

                        // Réessayer l'insertion par enregistrement individuel
                        $this->info("📋 Nouvelle tentative d'insertion des données...");
                        $retrySuccess = 0;
                        foreach ($data as $record) {
                            try {
                                DB::connection($to)->table($table)->insert((array) $record);
                                $retrySuccess++;
                            } catch (\Exception $retryError) {
                                $this->warn("⚠️  Échec réinsertion ID {$record->id}: " . $retryError->getMessage());
                            }
                        }
                        $this->info("✅ {$retrySuccess} enregistrements insérés après recréation de table");
                        return;
                    } else {
                        $this->warn("⚠️  Échec insertion enregistrement ID {$record->id}: " . $errorMessage);
                    }
                }
            }

            if ($errorCount > 0) {
                $this->warn("⚠️  {$errorCount} enregistrements ont échoué sur {$data->count()}");
            }

            $this->info("✅ {$data->count()} enregistrements synchronisés pour {$table}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la synchronisation de {$table}: " . $e->getMessage());
        }
    }

    private function ensureTableExists($table, $from, $to)
    {
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
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la vérification/création de la table {$table}: " . $e->getMessage());
            throw $e;
        }
    }

    private function createTableFromSource($table, $from, $to)
    {
        // Créer la table en copiant la structure depuis la source
        $this->info("📋 Création de la table {$table} en copiant la structure depuis {$from}");

        try {
            // Obtenir la structure de la table source
            $columns = DB::connection($from)->select("
                SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns
                WHERE table_name = ? AND table_schema = 'public'
                ORDER BY ordinal_position
            ", [$table]);

            if (empty($columns)) {
                throw new \Exception("Impossible d'obtenir la structure de la table {$table}");
            }

            // Construire la requête CREATE TABLE
            $createSql = $this->buildCreateTableSql($table, $columns);

            // Exécuter la création de table dans la destination
            DB::connection($to)->statement($createSql);

            $this->info("✅ Table {$table} créée avec succès dans {$to}");

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la création de la table {$table}: " . $e->getMessage());
            throw $e;
        }
    }

    private function buildCreateTableSql($tableName, $columns)
    {
        $sql = "CREATE TABLE IF NOT EXISTS \"{$tableName}\" (";

        $columnDefs = [];
        $primaryKey = null;

        foreach ($columns as $column) {
            $colDef = "\"{$column->column_name}\" ";

            // Mapper les types PostgreSQL vers SQL standard
            switch ($column->data_type) {
                case 'uuid':
                    $colDef .= 'UUID';
                    if ($column->column_name === 'id') {
                        $primaryKey = 'id';
                    }
                    break;
                case 'integer':
                    $colDef .= 'INTEGER';
                    break;
                case 'bigint':
                    $colDef .= 'BIGINT';
                    break;
                case 'character varying':
                case 'varchar':
                    $colDef .= 'VARCHAR(255)';
                    break;
                case 'text':
                    $colDef .= 'TEXT';
                    break;
                case 'timestamp without time zone':
                    $colDef .= 'TIMESTAMP';
                    break;
                case 'boolean':
                    $colDef .= 'BOOLEAN';
                    break;
                case 'json':
                    $colDef .= 'JSON';
                    break;
                default:
                    $colDef .= 'VARCHAR(255)'; // fallback
            }

            if ($column->is_nullable === 'NO') {
                $colDef .= ' NOT NULL';
            }

            // Simplifier les valeurs par défaut pour éviter les problèmes de séquences
            if ($column->column_default !== null && !str_contains($column->column_default, 'nextval')) {
                // Échapper les valeurs par défaut simples
                if (str_contains($column->column_default, '::')) {
                    $defaultValue = explode('::', $column->column_default)[0];
                    $colDef .= " DEFAULT {$defaultValue}";
                }
            }

            $columnDefs[] = $colDef;
        }

        // Ajouter la clé primaire si elle existe
        if ($primaryKey) {
            $sql .= implode(', ', $columnDefs) . ", PRIMARY KEY (\"{$primaryKey}\")";
        } else {
            $sql .= implode(', ', $columnDefs);
        }

        $sql .= ')';

        // Debug: Afficher le SQL généré
        $this->info("🔍 SQL généré: " . $sql);

        return $sql;
    }
}
<?php

// Script de test de connexion à la base de données
require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

// Configuration de la base de données
$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => $_ENV['DB_HOST'] ?? 'ep-misty-bar-a4j2hw28-pooler.us-east-1.aws.neon.tech',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'database' => $_ENV['DB_DATABASE'] ?? 'neondb',
    'username' => $_ENV['DB_USERNAME'] ?? 'neondb_owner',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8',
    'prefix' => '',
    'sslmode' => $_ENV['DB_SSLMODE'] ?? 'require',
    'options' => [
        PDO::ATTR_TIMEOUT => $_ENV['DB_CONNECTION_TIMEOUT'] ?? 60,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== Test de connexion à la base de données PostgreSQL ===\n\n";

try {
    // Test de connexion simple
    $pdo = $capsule->getConnection()->getPdo();
    echo "✅ Connexion PDO réussie\n";

    // Test de requête simple
    $result = $pdo->query("SELECT version()")->fetch();
    echo "✅ Requête SQL réussie\n";
    echo "📋 Version PostgreSQL: " . $result['version'] . "\n";

    // Test de connexion Eloquent
    $tables = $capsule->getConnection()->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    echo "✅ Connexion Eloquent réussie\n";
    echo "📋 Nombre de tables trouvées: " . count($tables) . "\n";

    if (count($tables) > 0) {
        echo "📋 Tables présentes:\n";
        foreach ($tables as $table) {
            echo "   - " . $table->table_name . "\n";
        }
    }

    echo "\n🎉 Test de connexion terminé avec succès!\n";

} catch (Exception $e) {
    echo "❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "🔍 Code d'erreur: " . $e->getCode() . "\n";

    if ($e->getCode() == 7) {
        echo "💡 Conseil: Vérifiez que les variables d'environnement DB_* sont correctement configurées\n";
    } elseif (strpos($e->getMessage(), 'SSL') !== false) {
        echo "💡 Conseil: Problème SSL - vérifiez la configuration SSLMODE\n";
    } elseif (strpos($e->getMessage(), 'timeout') !== false) {
        echo "💡 Conseil: Timeout de connexion - vérifiez la disponibilité du serveur\n";
    }

    exit(1);
}

echo "\n=== Configuration utilisée ===\n";
echo "Host: " . ($_ENV['DB_HOST'] ?? 'ep-misty-bar-a4j2hw28-pooler.us-east-1.aws.neon.tech') . "\n";
echo "Port: " . ($_ENV['DB_PORT'] ?? '5432') . "\n";
echo "Database: " . ($_ENV['DB_DATABASE'] ?? 'neondb') . "\n";
echo "Username: " . ($_ENV['DB_USERNAME'] ?? 'neondb_owner') . "\n";
echo "SSL Mode: " . ($_ENV['DB_SSLMODE'] ?? 'require') . "\n";
echo "Timeout: " . ($_ENV['DB_CONNECTION_TIMEOUT'] ?? '60') . " secondes\n";
?>
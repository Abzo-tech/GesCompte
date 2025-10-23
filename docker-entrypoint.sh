#!/bin/sh
set -e

# Attendre que PostgreSQL soit prêt
if command -v pg_isready > /dev/null; then
    until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" > /dev/null 2>&1; do
        echo "Database is unavailable - sleeping"
        sleep 2
    done
else
    echo "pg_isready not found, skipping database check"
fi

# Générer la clé d'application si absente
php artisan key:generate --force

# Mettre en cache config, routes et views
php artisan config:cache
php artisan route:cache
php artisan view:cache || true  # éviter l'échec si pas de vues

# Lancer le serveur Laravel
php artisan serve --host=0.0.0.0 --port=8000

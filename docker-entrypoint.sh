#!/bin/sh
set -e

# Attendre que PostgreSQL soit prêt avec timeout
if command -v psql > /dev/null; then
    attempts=0
    max_attempts=30  # 30 * 2 = 60 secondes max
    until PGPASSWORD="${DB_PASSWORD}" psql "postgresql://${DB_USERNAME}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_DATABASE}?sslmode=require" -c "SELECT 1;" > /dev/null 2>&1 || [ $attempts -ge $max_attempts ]; do
        echo "Database is unavailable - sleeping (attempt $((attempts+1))/$max_attempts)"
        sleep 2
        attempts=$((attempts+1))
    done
    if [ $attempts -ge $max_attempts ]; then
        echo "Warning: Database still unavailable after $max_attempts attempts, proceeding anyway"
    fi
else
    echo "psql not found, skipping database check"
fi

# Exécuter les migrations de base de données
php artisan migrate --force

# Générer la clé d'application si absente
php artisan key:generate --force

# Mettre en cache config, routes et views
php artisan config:cache
php artisan route:cache
php artisan view:cache || true  # éviter l'échec si pas de vues

# Générer la documentation
php artisan l5-swagger:generate --all || echo "Warning: Swagger generation failed but continuing..."

# Lancer le serveur Laravel
php artisan serve --host=0.0.0.0 --port=8000

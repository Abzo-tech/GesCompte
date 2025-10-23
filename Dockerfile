# Étape 1 : Image PHP officielle avec Composer
FROM php:8.3-fpm

# Installer les extensions nécessaires à Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer globalement
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copier le contenu du projet dans le conteneur
WORKDIR /var/www/html
COPY . .

# Installer les dépendances Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Donner les bons droits à Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Exposer le port attendu par Render
EXPOSE 10000

# Lancer Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000

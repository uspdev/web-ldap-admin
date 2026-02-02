#!/bin/bash
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

composer install --no-dev --optimize-autoloader --no-interaction

php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache

php artisan migrate --force

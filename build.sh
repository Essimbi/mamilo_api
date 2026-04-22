#!/usr/bin/env bash
# Exit on error
set -o errexit

composer install --no-dev --optimize-autoloader

# On ne peut pas migrer ici car la DB n'est pas forcément prête au build
# php artisan migrate --force 
php artisan config:cache
php artisan route:cache
php artisan view:cache
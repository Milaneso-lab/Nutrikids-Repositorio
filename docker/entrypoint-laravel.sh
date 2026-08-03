#!/bin/bash
set -e
cd /var/www/html

php artisan config:clear
php artisan cache:clear || true
php artisan package:discover --ansi || true
php artisan migrate --force

exec apache2-foreground

#!/bin/bash
set -e
cd /var/www/html

php artisan config:clear
php artisan cache:clear || true
php artisan package:discover --ansi || true
php artisan migrate --force

# Railway ha mostrado capas de build mezcladas donde mods-enabled/ conserva
# mpm_event de la imagen base junto al mpm_prefork que necesita mod_php;
# Apache rechaza arrancar con dos MPM cargados. Forzar el estado correcto
# en cada arranque de contenedor, no solo en build time.
rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
      /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf
ln -sf ../mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf ../mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

exec apache2-foreground

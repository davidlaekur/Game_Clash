#!/usr/bin/env sh
#
# Se ejecuta automáticamente al arrancar el contenedor (mecanismo de
# entrypoint.d de serversideup/php). Cachea config, rutas y vistas de Laravel
# para producción. Si algo crítico falla, queda registrado en los logs de
# Railway (stderr) para poder depurarlo.
#
# La imagen bookworm ejecuta los scripts de entrypoint.d con dash (POSIX sh),
# que NO soporta "pipefail" (es de bash). Usamos solo "set -eu", compatible.
set -eu

cd /var/www/html

echo "[entrypoint] Laravel: cacheando configuración para producción..."

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[entrypoint] Laravel: caché lista."

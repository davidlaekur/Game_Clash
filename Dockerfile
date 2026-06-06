# syntax=docker/dockerfile:1

###############################################################################
# Clash of Games — Laravel 10 + MongoDB
#
# Imagen base: serversideup/php (nginx + php-fpm ya configurados para Laravel,
# webroot en /var/www/html/public, usuario www-data, healthcheck incluido).
# Sobre ella instalamos la extensión nativa ext-mongodb (requerida por
# mongodb/laravel-mongodb), las dependencias de Composer y compilamos los
# assets de Vite.
###############################################################################

# ---------- Stage 1: build de assets con Node ----------
FROM node:20-alpine AS assets

WORKDIR /app

# Solo lo necesario para resolver e instalar dependencias de front
COPY package.json package-lock.json ./
RUN npm ci

# Copiamos el resto del código que necesita Vite (config, resources, etc.)
COPY . .
RUN npm run build


# ---------- Stage 2: aplicación PHP (nginx + php-fpm) ----------
# IMPORTANTE: usamos el tag -bookworm (Debian 12, OpenSSL 3.0) en vez del tag
# flotante 8.3-fpm-nginx (que pasó a Debian 13 trixie con OpenSSL 3.5.6).
# OpenSSL 3.5 rompe el handshake TLS contra los clusters compartidos M0 de Atlas
# ("tlsv1 alert internal error" / SSL alert 80), incluso con openssl s_client
# directo. OpenSSL 3.0 (bookworm) negocia correctamente con Atlas M0.
FROM serversideup/php:8.3-fpm-nginx-bookworm

# Extensión MongoDB de PHP (rama 2.x, fijada en composer.lock).
USER root
RUN install-php-extensions mongodb
USER www-data

WORKDIR /var/www/html

# Copiamos el código de la aplicación (con permisos del usuario www-data)
COPY --chown=www-data:www-data . .

# Dependencias de PHP de producción. El composer.lock ya está fijado a la rama
# Mongo 2.x (mongodb/mongodb 2.3, laravel-mongodb 5.7), que usa ext-mongodb 2.x
# y negocia bien el TLS con Atlas. Instalación determinista desde el lock.
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Assets ya compilados desde el stage de Node
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Script que cachea la config de Laravel al arrancar (entrypoint.d de la imagen)
USER root
COPY deploy/10-laravel-cache.sh /etc/entrypoint.d/10-laravel-cache.sh
RUN chmod +x /etc/entrypoint.d/10-laravel-cache.sh
USER www-data

# serversideup/php expone el 8080 por defecto y arranca nginx + php-fpm vía s6.
EXPOSE 8080

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
FROM serversideup/php:8.3-fpm-nginx

# Instalar la extensión MongoDB de PHP (root para pecl, luego volvemos a www-data)
USER root
RUN install-php-extensions mongodb
USER www-data

WORKDIR /var/www/html

# Copiamos el código de la aplicación (con permisos del usuario www-data)
COPY --chown=www-data:www-data . .

# Dependencias de PHP de producción
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

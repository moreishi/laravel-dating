# syntax=docker/dockerfile:1

# ── Stage 1: Build frontend assets ────────────────────────────────
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install --ignore-scripts

COPY vite.config.js* tailwind.config.* postcss.config.* ./
COPY resources/ ./resources/
RUN npm run build

# ── Stage 2: Composer install (production only) ──────────────────
FROM composer:2 AS composer

WORKDIR /app
COPY composer.json composer.lock* ./
RUN composer install --no-dev --no-interaction --no-scripts --optimize-autoloader --no-ansi

# ── Stage 3: Production image ────────────────────────────────────
FROM serversideup/php:8.4-fpm-nginx

LABEL maintainer="LaraDate"

ENV APP_ENV=production
ENV APP_DEBUG=false

WORKDIR /var/www/html

# Install PHP extensions (switch to root for install-php-extensions)
USER root
RUN install-php-extensions pdo_mysql bcmath gd intl zip
USER www-data

# Copy composer dependencies
COPY --from=composer /app/vendor ./vendor

# Copy application source
COPY --chown=9999:9999 . .

# Copy built assets
COPY --from=assets --chown=9999:9999 /app/public/build ./public/build

# Generate optimized caches
RUN php artisan optimize --no-interaction

# Set permissions
RUN chown -R 9999:9999 storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80 443

HEALTHCHECK --interval=10s --timeout=3s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Stage 1: Compile Vite theme assets (admin, client, portal)
FROM node:22-alpine AS asset-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npx vite build --config=resources/themes/admin/moraine/vite.config.js && \
    npx vite build --config=resources/themes/client/moraine/vite.config.js && \
    npx vite build --config=resources/themes/portal/moraine/vite.config.js

# Stage 2: Production (PHP 8.3 FPM + Nginx via serversideup/php)
FROM serversideup/php:8.3-fpm-nginx

LABEL org.opencontainers.image.title="Billmora"
LABEL org.opencontainers.image.description="Billmora — Billing & Automation Platform"
LABEL org.opencontainers.image.source="https://github.com/billmora/billmora"
LABEL org.opencontainers.image.licenses="BUSL-1.1"

WORKDIR /var/www/html

USER root

# PHP extensions required by linux-server.md
RUN install-php-extensions \
    bcmath \
    curl \
    gd \
    intl \
    mbstring \
    pdo_mysql \
    redis \
    xml \
    zip

COPY --from=asset-builder --chown=www-data:www-data /app /var/www/html

COPY .github/docker/entrypoint.sh /docker-entrypoint.d/10-ensure-env.sh
RUN chmod +x /docker-entrypoint.d/10-ensure-env.sh

RUN rm -rf node_modules tests .git .github

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

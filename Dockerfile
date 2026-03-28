# Stage 1: Build Assets (Compile Vite themes)
FROM node:22-alpine AS build
WORKDIR /app

# Copy only package files first to leverage Docker layer caching
COPY package*.json ./
RUN npm ci

# Copy the rest of the application and build themes
COPY . .
RUN npx vite build --config=resources/themes/admin/moraine/vite.config.js
RUN npx vite build --config=resources/themes/client/moraine/vite.config.js
RUN npx vite build --config=resources/themes/portal/moraine/vite.config.js

# Stage 2: Production PHP
FROM serversideup/php:8.3-fpm-nginx
WORKDIR /var/www/html

# Switch to root to install mandatory PHP extensions
USER root
RUN install-php-extensions intl gd pdo_mysql redis zip

# Copy the application from the build stage
COPY --from=build --chown=www-data:www-data /app /var/www/html

# Clean up unnecessary files to keep image size small
RUN rm -rf /var/www/html/node_modules /var/www/html/tests /var/www/html/.git

# Switch back to the standard user
USER www-data

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions for storage and bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose port 8080 (serversideup default)
EXPOSE 8080

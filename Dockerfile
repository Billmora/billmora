# Use serversideup/php:8.3-fpm-nginx for a production-ready Laravel setup
FROM serversideup/php:8.3-fpm-nginx

# Set the working directory
WORKDIR /var/www/html

# Switch to root to install dependencies if needed
USER root

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN install-php-extensions intl gd pdo_mysql redis zip

# Switch back to the standard user
USER www-data

# Copy the application code
COPY --chown=www-data:www-data . .

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set permissions for storage and bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Expose port 8080 (serversideup default)
EXPOSE 8080

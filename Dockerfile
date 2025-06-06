# Base image: PHP 8.3 with CLI
FROM php:8.3-cli

# Install system dependencies
RUN apt-get update -y && apt-get install -y \
    unzip \
    zip \
    git \
    libzip-dev \
    libicu-dev \
    zlib1g-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql intl zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www

# Expose port
EXPOSE 8000
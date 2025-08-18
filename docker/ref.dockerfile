# ---- Stage 1: Build environment ----
FROM php:7.4-cli AS builder

RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install mbstring pdo pdo_mysql zip

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Install Oil CLI
RUN curl -L https://get.fuelphp.com/oil | sh && \
    mv oil /usr/local/bin/oil && chmod +x /usr/local/bin/oil

WORKDIR /app

# Create FuelPHP project
RUN composer create-project fuel/fuel . --no-git --version=1.8.1

# Add your actual app code (replace these with COPY if local)
# COPY ./fuel/app ./fuel/app
# COPY ./public ./public

# ---- Stage 2: Production container ----
FROM php:7.4-apache

RUN a2enmod rewrite

# Only required extensions
RUN apt-get update && apt-get install -y \
    libzip-dev libpng-dev libonig-dev \
    && docker-php-ext-install mbstring pdo pdo_mysql zip

WORKDIR /var/www/html

# Copy built app from builder
COPY --from=builder /app /var/www/html

# Permissions
RUN chown -R www-data:www-data . && chmod -R 755 .

# Enable .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

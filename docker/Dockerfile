FROM php:7.4-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    locales \
    && docker-php-ext-install pdo pdo_mysql mbstring xml zip

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

# Install Oil CLI globally
RUN curl -L https://get.fuelphp.com/oil | sh

# Set working directory
WORKDIR /var/www/html

# Install FuelPHP
RUN composer create-project fuel/fuel:1.8.1 . --no-install --remove-vcs

COPY composer.json /var/www/html/
COPY composer.lock /var/www/html/

RUN composer config --no-plugins allow-plugins true
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader


RUN rm -rf /var/www/html/public /var/www/html/fuel/app
COPY public /var/www/html/public
COPY app /var/www/html/fuel/app
COPY webserver/fuelapp.htaccess /var/www/html/fuel/.htaccess

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache
COPY webserver/apache2.conf /etc/apache2/apache2.conf
COPY webserver/vhost.conf /etc/apache2/sites-available/000-default.conf
# Point DocumentRoot to fuel public directory
# RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|' /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite
EXPOSE 80


CMD ["apache2-foreground"]
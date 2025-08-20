FROM php:7.4-apache

# Install required packages including locale support
RUN apt-get update && apt-get install -y \
    locales \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysqli mbstring xml zip

# Set up locale
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen \
    && locale-gen en_US.UTF-8 \
    && update-locale LANG=en_US.UTF-8

# Set environment variables for locale
ENV LANG=en_US.UTF-8
ENV LANGUAGE=en_US:en
ENV LC_ALL=en_US.UTF-8

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

# Replace default public and app directories
RUN rm -rf /var/www/html/public /var/www/html/fuel/app
COPY public /var/www/html/public
COPY app /var/www/html/fuel/app
COPY webserver/fuelapp.htaccess /var/www/html/fuel/.htaccess

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache config
COPY webserver/apache2.conf /etc/apache2/apache2.conf
COPY webserver/vhost.conf /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]

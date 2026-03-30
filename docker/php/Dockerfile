FROM php:8.3-apache


RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite
RUN a2enmod rewrite

# Config Apache
COPY docker/php/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html


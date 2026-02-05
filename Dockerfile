FROM php:8.3-apache

COPY dokku-deploy/ldap.conf /etc/ldap/ldap.conf

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    unixodbc \
    unixodbc-dev \
    freetds-dev \
    freetds-bin \
    tdsodbc \
    libsybdb5 \
    libldap2-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && ln -s /usr/lib/x86_64-linux-gnu/libsybdb.a /usr/lib/ \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN usermod -d /var/www/html www-data

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/

RUN docker-php-ext-install \
    pdo_mysql \
    pdo_dblib \
    ldap \
    gd \
    mbstring \
    zip \
    xml \
    bcmath \
    pcntl \
    opcache

RUN echo "[FreeTDS]" >> /etc/odbcinst.ini \
    && echo "Description = FreeTDS Driver" >> /etc/odbcinst.ini \
    && echo "Driver = /usr/lib/x86_64-linux-gnu/odbc/libtdsodbc.so" >> /etc/odbcinst.ini \
    && echo "Setup = /usr/lib/x86_64-linux-gnu/odbc/libtdsS.so" >> /etc/odbcinst.ini

RUN a2enmod rewrite

COPY dokku-deploy/apache-php.conf /etc/apache2/conf-available/
RUN a2enconf apache-php

RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

COPY composer.json composer.lock ./

USER www-data
ENV COMPOSER_HOME=/var/www/.composer
RUN composer install --no-interaction --no-dev --no-autoloader

USER root

COPY --chown=www-data:www-data . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Finalizar autoload
USER www-data
RUN composer dump-autoload

EXPOSE 80

CMD ["apache2-foreground"]

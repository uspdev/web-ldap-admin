FROM uspdev/uspdev-php-apache:8.4

RUN mkdir -p /etc/ldap && echo "TLS_REQCERT ALLOW" > /etc/ldap/ldap.conf

RUN sed -i 's|/var/www/html|/var/www/html/public|' \
    /etc/apache2/sites-available/000-default.conf

RUN echo "[FreeTDS]" >> /etc/odbcinst.ini \
    && echo "Description = FreeTDS Driver" >> /etc/odbcinst.ini \
    && echo "Driver = /usr/lib/x86_64-linux-gnu/odbc/libtdsodbc.so" >> /etc/odbcinst.ini \
    && echo "Setup = /usr/lib/x86_64-linux-gnu/odbc/libtdsS.so" >> /etc/odbcinst.iniv

# linha necessária para instalação da biblioteca replicado
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

# LDAP dependencies
RUN apt-get update && apt-get install -y libldap2-dev \
        && rm -rf /var/lib/apt/lists/* \
        && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
        && docker-php-ext-install ldap

USER www-data

COPY --chown=www-data . .

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

CMD ["apache2-foreground"]

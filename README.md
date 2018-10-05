# myldap

Install: 

    php-ldap

Compile Assets:
    
    php artisan vendor:publish --provider="JeroenNoten\LaravelAdminLte\ServiceProvider" --tag=assets

## Dicas

No ambiente de desenvolvimento, as vezes é necessário desativar a verificação dos certificado SSL/TLS,
para isso: 

    #/etc/ldap/ldap.conf
    TLS_REQCERT ALLOW
 

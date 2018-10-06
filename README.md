# Cenário

Você usa uma base ldap para controlar acesso e regras às máquinas do seu parque
computacional. Mas sabendo que a base de usuários já existe no seu banco de dados replicado,
manter suas base assíncrona se torna uma dor de cabeça.

Esse sistema permite:

 - Sincronizar base de dados ldap local com pessoas importadas do replicado USP
 - Permite o próprio usuário trocar senha ldap pela web
 - Gerenciar usuários locais no ldap que não estejam no replicado

Instalação:

    php-ldap

Compile Assets:

    php artisan vendor:publish --provider="JeroenNoten\LaravelAdminLte\ServiceProvider" --tag=assets

## Dicas

No ambiente de desenvolvimento, as vezes é necessário desativar a verificação dos certificado SSL/TLS,
para isso: 

    #/etc/ldap/ldap.conf
    TLS_REQCERT ALLOW
 

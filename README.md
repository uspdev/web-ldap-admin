# web-ldap-admin

Sistema escrito em laravel que permite gerenciar usuários da sua
unidade na Universidade de São Paulo em uma base local do tipo domain controller samba/ad.
Para tal, dois serviços são necessários para rodar esse sistema: 
banco de dados corporativa replicada e oauth 1.0 senha única.

Quando o usuário fizer login no sistema usando senha única, uma array $vinculo
é retornado com a seguinte estrutura:

    0 => array:9 [
      "tipoVinculo" => "ALUNOCEU"
      "codigoSetor" => 0
      "nomeAbreviadoSetor" => null
      "nomeSetor" => null
      "codigoUnidade" => 8
      "siglaUnidade" => "FFLCH"
      "nomeUnidade" => "Faculdade de Filosofia, Letras e Ciências Humanas"
      "nomeVinculo" => "Aluno de Cultura Extensão Universitária"
      "nomeAbreviadoFuncao" => null
    ]
    1 => array:9 [
      "tipoVinculo" => "ALUNOGR"
      "codigoSetor" => 0
      "nomeAbreviadoSetor" => null
      "nomeSetor" => null
      "codigoUnidade" => 8
      "siglaUnidade" => "FFLCH"
      "nomeUnidade" => "Faculdade de Filosofia, Letras e Ciências Humanas"
      "nomeVinculo" => "Aluno de Graduação"
      "nomeAbreviadoFuncao" => null
    ]
    2 => array:9 [
      "tipoVinculo" => "ESTAGIARIORH"
      "codigoSetor" => 606
      "nomeAbreviadoSetor" => "SCINFOR-08"
      "nomeSetor" => "Seção Técnica de Informática"
      "codigoUnidade" => 8
      "siglaUnidade" => "FFLCH"
      "nomeUnidade" => "Faculdade de Filosofia, Letras e Ciências Humanas"
      "nomeVinculo" => "Estagiário"
      "nomeAbreviadoFuncao" => "Estagiário"
    ]

Se a pessoa tiver algum vínculo (codigoUnidade) com a unidade (REPLICADO_UNIDADE)
o usuário é inserido no domain controller. 
Os campos tipoVinculo e nomeAbreviadoSetor serão mapeados com grupos.

Os usuários, após entrarem com senha única, podem trocar a senha do ldap local.

## Instalação

Dependências php:

    version='7.3'
    apt-get install php$version-ldap
 
Informações no arquivo .env referente ao servidor domain controller:

    LDAP_HOSTS=dc.xurepinha.br
    LDAP_PORT=636
    LDAP_BASE_DN='DC=pandora,DC=fflch,DC=usp,DC=br'
    LDAP_USERNAME='CN=Administrator,CN=Users,DC=xurepinha,DC=br'
    LDAP_PASSWORD='sua-senha'
    LDAP_USE_SSL=true
    LDAP_USE_TLS=false
    
Informações no arquivo .env referente ao serviço de senha única OAuth 1.0:

    SENHAUNICA_KEY=oh-man
    SENHAUNICA_SECRET=secret
    SENHAUNICA_CALLBACK_ID=100

Informações no arquivo .env referente ao serviço de senha única OAuth 1.0:

    REPLICADO_HOST=
    REPLICADO_PORT=
    REPLICADO_DATABASE=
    REPLICADO_USERNAME=
    REPLICADO_PASSWORD=
    REPLICADO_UNIDADE=8

Esse sistema permite:

 - Sincronizar base de dados ldap local com pessoas importadas do replicado USP
 - Permite o próprio usuário trocar senha ldap pela web
 - Gerenciar usuários locais no ldap que não estejam no replicado

Instalação:

    composer install
    php artisan key:generate
    php artisan migrate

Publicar Assets:

    php artisan vendor:publish --provider="Uspdev\UspTheme\ServiceProvider" --tag=assets --force
    php artisan vendor:publish --provider="Adldap\Laravel\AdldapAuthServiceProvider" --tag=assets --force
    php artisan vendor:publish --provider="Adldap\Laravel\AdldapServiceProvider" --tag=assets --force

## Dicas

No ambiente de desenvolvimento, as vezes é necessário desativar a verificação dos certificado SSL/TLS,
para isso: 

    #/etc/ldap/ldap.conf
    TLS_REQCERT ALLOW

Como rodar filas sem limite de tempo:

    php artisan queue:listen --timeout=0
 

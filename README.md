# web-ldap-admin

Sistema escrito em laravel que permite gerenciar usuários da sua
unidade na Universidade de São Paulo em uma base local do tipo 
domain controller implementada em samba-ad-dc ou Active Directory. 
Para tal, dois serviços são necessários para rodar esse sistema: 
banco de dados corporativa replicada (sybase ou mssql) e tokens do OAuth 1 para senha única.

Esse sistema permite:

 - Sincronizar base de dados ldap local com pessoas importadas do replicado USP
 - O próprio usuário trocar senha ldap pela web
 - Gerenciar usuários locais no ldap que não estejam no replicado

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

Se a pessoa tiver algum vínculo (codigoUnidade) com a unidade
o usuário é inserido no domain controller. 
Os campos tipoVinculo e nomeAbreviadoSetor serão mapeados com grupos.

## Instalação

### Dependências php

    version='7.3'
    apt-get install php$version-ldap

### Configurações no .env

Copie o arquivo .env.example para .env e faça os ajustes necessários.

    cp .env.example .env

Servidor domain controller:

    LDAP_HOSTS=dc.xurepinha.br
    LDAP_PORT=636
    LDAP_BASE_DN='DC=pandora,DC=fflch,DC=usp,DC=br'
    LDAP_USERNAME='CN=Administrator,CN=Users,DC=xurepinha,DC=br'
    LDAP_PASSWORD='sua-senha'
    LDAP_USE_SSL=true
    LDAP_USE_TLS=false

O LDAP_USERNAME pode ter variações. Na biblioteca adldap2 indica o uso de usuario@xurepiha.br.
Também pode ser usado a sintaxe de domínio anterior ao AD xurepinha\\\\usuario.
    
COnfiguração referente à OAuth1 (http://github.com/uspdev/senhaunica-socialite)

    SENHAUNICA_KEY=oh-man
    SENHAUNICA_SECRET=secret
    SENHAUNICA_CALLBACK_ID=100

Configuração referente ao replicado (http://github.com/uspdev/replicado)

    REPLICADO_HOST=
    REPLICADO_PORT=
    REPLICADO_DATABASE=
    REPLICADO_USERNAME=
    REPLICADO_PASSWORD=
    REPLICADO_CODUNDCLG=8

Configuração referente ao processo de sincronização de dados do usuário durante o login no sistema (0 = desativado / 1 = ativado)

    SINC_LDAP_LOGIN=1
    
### Dependências do composer

    composer install

### Configurações do laravel

    php artisan key:generate
    php artisan migrate

## Dicas

No ambiente de desenvolvimento, às vezes é necessário desativar a verificação 
dos certificado SSL/TLS, para isso, em /etc/ldap/ldap.conf manter apenas: 

    TLS_REQCERT ALLOW

Como rodar filas sem limite de tempo:

    php artisan queue:listen --timeout=0

Caso o deploy do sistema seja realizado em contexto e não na raiz do domínio 
talvez seja necessário habilitar a diretiva RewriteBase no arquivo public/.htaccess: 

    RewriteEngine On
    RewriteBase "/<CONTEXTO>/"
    ...
         
## Códigos Úteis

Ativar toda base de usuários:

    php artisan tinker

    $users = \Adldap\Laravel\Facades\Adldap::search()->users()->get();

    foreach($users as $user) {
        $user->setUserAccountControl(512);
        $user->save();
    }

Rodar um job pelo tinker:

    php artisan tinker
    App\Jobs\RevokeLocalAdminGroupJob::dispatch();

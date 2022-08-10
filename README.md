# web-ldap-admin

Sistema escrito em laravel que permite gerenciar usuários da sua
unidade na Universidade de São Paulo em uma base local do tipo
domain controller implementada em samba-ad-dc ou Active Directory.
Para tal, dois serviços são necessários para rodar esse sistema:
banco de dados corporativa replicada (sybase ou mssql) e tokens do OAuth 1 para senha única.

Esse sistema permite:

 - Sincronizar base de dados ldap local com pessoas importadas do replicado USP
 - O próprio usuário pode trocar a senha ldap pela web
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
o usuário é inserido no domain controller. Os campos tipoVinculo e nomeAbreviadoSetor serão mapeados com grupos.

Caso não queira inserir automaticamente a pessoa, esse recurso pode ser desabilitado no .env.

## Dependências php

    version='7.3'
    apt-get install php$version-ldap

## Instalação

    git clone git@github.com/uspdev/php-ldap-admin
    composer install --no-dev (ambiente de produção)

## Configurações no .env

Copie o arquivo .env.example para .env e faça os ajustes necessários.

    cp .env.example .env
    php artisan key:generate
    # Configure o banco de dados local
    php artisan migrate

### Configurações da aplicação

#### Servidor domain controller

    LDAP_HOSTS=dc.xurepinha.br
    LDAP_PORT=636
    LDAP_BASE_DN='DC=pandora,DC=fflch,DC=usp,DC=br'
    LDAP_USERNAME='CN=Administrator,CN=Users,DC=xurepinha,DC=br'
    LDAP_PASSWORD='sua-senha'
    LDAP_USE_SSL=true
    LDAP_USE_TLS=false

O LDAP_USERNAME pode ter variações. Na biblioteca adldap2 indica o uso de usuario@xurepiha.br. Também pode ser usado a sintaxe de domínio anterior ao AD xurepinha\\\\usuario.

#### Sincronização automática do LDAP

Configuração referente ao processo de sincronização de dados do usuário durante o login no sistema.

* **0** - não cria pessoa ldap automaticamente no login;
* **1** (default) - cria pessoa ldap automaticamente no login e sincroniza dados com replicado. O valos existente nesses campos serão sobrescritos:
    * nome
    * sobrenome
    * email
    * employeeNumber: preenche com nro USP ser estiver configurado no env
    * departamento

Além disso, cria e coloca a pessoa nos grupos "vinculo estendido (tipvinext)", "setor" e "vinculo estendido setor". OBS.: Preserva os demais grupos já existentes.

    SINC_LDAP_LOGIN=1

#### Desativar desligados

No processo de sincronização o sistema pode desativar os usuários que não tem mais vínculos ativos com a unidade, a não ser que estejam listados nos Números USP permitidos sem vínculo. false (default) - não desativa desligados; true - desativa desligados

    DESATIVAR_DESLIGADOS=false

#### Organizational unit (OU) padrão

Define onde os usuários e grupos serão inseridos. É conveniente setar um valor aqui. Se vazio (default) vai criar na raiz do CN (conteiner).

OBS.: Aparentemente estando nesta OU ou no conteiner Users padrão, o usuário consegue fazer login normalmente. Mas acho que é importante no uso de diretivas de grupos.

    LDAP_OU_DEFAULT=

#### Expiração da senha

Ao criar conta nova ou trocar a senha, pode-se definir um prazo para expiração de conta padrão. Se 0 (default), a conta não vai expirar. O valor é em dias.

    EXPIRAR_EM=0

#### Campo associado ao codpes

Configura qual campo vai estar associado ao codpes da pessoa. Por padrão é no campo **username** mas pode ser atribuído ao campo **employeeNumber**. No segundo caso, na criação de novo usuário automático, o username vai ser o **email** sem caracteres especiais (somente letras e números), sem o domínio, limitado a 15 caracteres. Se o usuário já existir o username pode ser qualquer.

    CAMPO_CODPES=username

#### Padrão de criação de senhas

Configura como será criado a senha padrão para os novos usuários ldap. Pode ser a **data de nascimento** (default) ou **random**. O random é compatível com a diretiva de senha forte do AD.

    SENHA_PADRAO=data_nascimento

OBS.: Quando a pessoa não tem vínculo (dados replicados), pode logar e sincroniza/cria conta no login, a conta é criada com senha random, pois não está disponível a data de nascimento.

#### Permite assumir conta de admin

Permite que a pessoa obtenha acesso privilegiado a determinado computador por tempo limitado. 0 (default) - ninguém pode solicitar; 1 - todos; 2 - somente servidores (docentes e não docentes).

    SOLICITA_CONTA_ADMIN = 0

#### Mostrar foto

Permite buscar foto e exibir nas informações da pessoa. 0 (default) - não mostrar (nem foto fake); 1 - mostrar foto.

    MOSTRAR_FOTO=0

#### Trocar senha na criação de contas novas

Ao criar uma nova conta no ldap, ele força o usuário a trocar a senha no próximo logon do windows. Se o login com AD é usado em outros sistemas, como aplicações web ou em terminais com linux deixar essa opção como 0 (falsa).

    OBRIGA_TROCAR_SENHA_NO_WINDOWS=1

## Dicas

Nessa aplicação, SENHAUNICA_ADMINS pode gerenciar usuários, SENHAUNICA_GERENTES pode realizar as operações em geral e usuários comuns podem alterar suas respectivas senhas.

No ambiente de desenvolvimento, às vezes é necessário desativar a verificação
dos certificado SSL/TLS, para isso, em /etc/ldap/ldap.conf manter apenas:

    TLS_REQCERT ALLOW

Como rodar filas sem limite de tempo:

    php artisan queue:listen --timeout=0

## Códigos Úteis

Ativar toda base de usuários:

    php artisan tinker

    $users = \Adldap\Laravel\Facades\Adldap::search()->users()->get();

    foreach($users as $user) {
        $user->setUserAccountControl(AccountControl::NORMAL_ACCOUNT);
        $user->save();
    }

Rodar um job pelo tinker:

    php artisan tinker
    App\Jobs\RevokeLocalAdminGroupJob::dispatch();


## Funcionamento dos Grupos

O sistema vai adicionar o usuário ao grupo com o mesmo nome do vínculo. Ex.: ALUNOGR, SERVIDOR, etc. Os grupos são criados automaticamente.

* Se o grupo foi criado pelo web-ldap-admin ele seta o atributo managedBy=web-ldap-admin

Grupos criados:

* SETOR (codset)
* Vinculo-estendido (tipvinext)
* Vinculo-estendido + SETOR

O departamento (department) corresponde ao setor, se tiver.
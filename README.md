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

    version='7.4'
    sudo curl -sS https://getcomposer.org/download/1.10.26/composer.phar -o /usr/bin/composer # usa o composer v1.10 compatível com o PHP 7.4
    sudo chmod +x /usr/bin/composer                                                           # dá permissão de execução

## Instalação

    git clone git@github.com/uspdev/php-ldap-admin
    composer install --no-dev (ambiente de produção)

## Configurações no .env

Copie o arquivo .env.example para .env e faça os ajustes necessários.

    cp .env.example .env
    php artisan key:generate

Criar user e banco de dados (em mysql):

    sudo mysql
    create database webldapadmin;
    create user 'webldapadmin'@'%' identified by '<<password here>>';
    grant all privileges on *.* to 'webldapadmin'@'%';
    alter user 'webldapadmin'@'%' identified with mysql_native_password by '<<password here>>';
    flush privileges;

E também:

    php artisan migrate

Se ocorrer o erro "There is no permission named 'admin' for guard 'senhaunica'" ao logar no web-ldap-admin, rode:

    php artisan tinker
        $u = new App\Models\User;
        $u->criarPermissoesPadrao();

E verifique que a tabela permissions está populada. Se esse erro continuar acontecendo, limpe o cache do navegador.

### Configurações da aplicação

#### Servidor domain controller

    LDAP_HOSTS=dc.xurepinha.br
    LDAP_PORT=636
    LDAP_BASE_DN='DC=pandora,DC=fflch,DC=usp,DC=br'
    LDAP_USERNAME='CN=Administrator,CN=Users,DC=xurepinha,DC=br'
    LDAP_PASSWORD='sua-senha'
    LDAP_USE_SSL=true
    LDAP_USE_TLS=false

O LDAP_USERNAME pode ter variações. Na biblioteca adldap2 indica o uso de usuario@xurepinha.br. Também pode ser usado a sintaxe de domínio anterior ao AD xurepinha\\\\usuario.

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
dos certificado SSL/TLS, para isso, em /etc/ldap/ldap.conf manter apenas TLS_REQCERT ALLOW:

    echo 'TLS_REQCERT ALLOW' | sudo tee /etc/ldap/ldap.conf
    
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


#####################
## Funcionalidades ##
#####################

- ao logar no sistema, é exibida a mensagem "Informações sincronizadas com Sistemas Corporativos", querendo dizer que o usuário teve seus dados criados/atualizados no LDAP a partir do que está no Replicado;
- menu "Minha Conta (trocar senha da rede)": permite que o usuário altere sua senha no LDAP;
                                             permite que admins alterem senhas de usuários, obriguem que usuários alterem suas senhas no próximo logon, incluam usuários em grupos, configurem expiração de contas de usuários, desabilitem contas de usuários e excluam usuários... tudo isso na base do LDAP;
- menu "Sincronizar OU": permite que admins sincronizem o LDAP da OU com o Replicado;
                         permite que admins dêem acesso ao LDAP da OU a pessoas sem vínculo com a USP.


#################
## Observações ##
#################

- pelo fato de ser muito difícil (politicamente falando) transmitir as senhas únicas para os replicados das unidades, usa-se nos LDAPs locais uma senha desvinculada da senha única;
  assim sendo, não é possível fazer com que uma alteração de senha única implique em uma alteração automática de senha no LDAP;
  portanto, para alterar sua senha no LDAP, o usuário deve entrar no web-ldap-admin pelo smartphone.

- temos LDAP somente na pró-aluno;
  utilizaremos o web-ldap-admin somente para a pró-aluno;
  queremos levar para essa LDAP os dados do Replicado somente de "Aluno Convênio Interc Grad" e "Aluno de Graduação";
  portanto, na tela de sincronização com o Replicado, selecionar somente esses dois itens e clicar em "Sincronizar".

- quando adicionei o valor "proaluno" na variável de configuração LDAP_OU_DEFAULT no .env, e me inseri em um novo grupo no LDAP, o site conseguiu me gravar no LDAP;
  então existe a possibilidade de que esta configuração tenha resolvido o problema de conseguir gravar no LDAP;
  neste caso, não precisaremos trocar o LDAP de Windows para Samba;
  mas vamos deixar para testar isso nas férias escolares, para não corrermos o risco de causarmos algum problema na LDAP agora e prejudicarmos os alunos agora.

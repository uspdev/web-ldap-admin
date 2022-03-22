<?php

return [
    # Footer theme
    #'footer' => env('FOOTER', false),

    # Unidades autorizadas
    'replicado_unidade' => env('REPLICADO_CODUNDCLG'),

    # Paginação, quantidade de registros padrão, 50 default
    'registrosPorPagina' => env('REGISTROS_POR_PAGINA', 50),

    # Desativar desligados na sincronização (true/false default)
    'desativarDesligados' => env('DESATIVAR_DESLIGADOS', false),

    # foi desativado em 2/2022. Nesse caso ele mantem os grupos 
    # existentes e adiciona se necessário os demais grupos
    'notRemoveGroups' => env('NOT_REMOVE_GROUPS', 'LOCAL_ADMIN,STI'),

    # 0 = ninguém, 1 = todos, 2 = servidores (funcionários e docentes)
    'solicitaContaAdmin' => env('SOLICITA_CONTA_ADMIN', 0),

    # Grupo que o usuário será adicionado para ao solicitar conta de admin
    'localAdminGroupLdap' => env('LOCAL_ADMIN_GROUP_LDAP', 'LOCAL_ADMIN'),

    # sincroniza ldap com dados do replicado durante o login da pessoa
    # 0 - não sincroniza durante login; 1 - sincroniza durante login
    'sincLdapLogin' => env('SINC_LDAP_LOGIN', 1),

    # Unidade Organizacional padrão
    'ouDefault' => env('LDAP_OU_DEFAULT', ''),

    # Prazo padrão para expirar conta (dias). 0 para não expirar
    'expirarEm' => env('EXPIRAR_EM', 0),

    # Contas de usuarios ocultadas default do sistema
    'ocultarUsuarios' => ['administrator', 'administrador', 'krbtgt', 'guest'],

    # Campo LDAP que será usado como codpes
    # username, employeeNumber 
    # vai ser aplicado strtolower então o case não importa
    'campoCodpes' => env('CAMPO_CODPES','username'),

    # Tipo padrão de senha
    # data_nascimento, random
    'senhaPadrao' => env('SENHA_PADRAO','data_nascimento'),

    # Mostrar WsFoto? Default 0 = não
    'mostrarFoto' => env('WSFOTO', 0),

    # Forçar trocar senha no pŕoximo login do windows
    # se o login com AD é usado em outros sistemas, como aplicações web 
    # ou em terminais com linux deixar essa opção como 0 (falsa)
    # pois o usuário fica travado e não consegue logar em nada a não ser nos windows.
    # Por default está 1 (true) pois assim estava no Ldap/User.php
    # Válido para criação de contassincronizadas
    'obrigaTrocarSenhaNoWindows' => env('OBRIGA_TROCAR_SENHA_NO_WINDOWS', 1),
];

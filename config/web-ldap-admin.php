<?php

return [
    # Footer theme
    #'footer' => env('FOOTER', false),

    # Unidades autorizadas
    'replicado_unidade' => env('REPLICADO_CODUNDCLG'),

    # Paginação, quantidade de registros padrão, 50 default
    'registrosPorPagina' => env('REGISTROS_POR_PAGINA', 50),

    # Desativar desligados (true/false default)
    'desativarDesligados' => env('DESATIVAR_DESLIGADOS', false),

    'localAdminGroupLdap' => env('LOCAL_ADMIN_GROUP_LDAP', 'LOCAL_ADMIN'),

    'notRemoveGroups' => env('NOT_REMOVE_GROUPS', 'LOCAL_ADMIN,STI'),

    # 0 = ninguém, 1 = todos, 2 = servidores (funcionários e docentes)
    'solicitaContaAdmin' => env('SOLICITA_CONTA_ADMIN', 0),

    # 0 = não sincroniza durante login, 1 = sincroniza durante login
    'sincLdapLogin' => env('SINC_LDAP_LOGIN', 1),

    # Unidade Organizacional padrão
    'ouDefault' => env('LDAP_OU_DEFAULT', ''),

    # Prazo padrão para expirar conta (dias). 0 para não expirar
    'expirarEm' => env('EXPIRAR_EM', 0),

    # Contas de usuarios ocultadas default do sistema
    'ocultarUsuarios' => ['administrator', 'administrador', 'krbtgt', 'guest'],

    # Campo LDAP que será usado como codpes
    # employeeNumber, username
    # vai ser aplicado strtolower então o case não importa
    'campoCodpes' => env('CAMPO_CODPES','username'),

    # Tipo padrão de senha
    # data_nascimento, random
    'senhaPadrao' => env('SENHA_PADRAO','data_nascimento'),

    # Mostrar WsFoto? Default 0 = não
    'mostrarFoto' => env('WSFOTO', 0),
];

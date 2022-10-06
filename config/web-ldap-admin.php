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

    # Grupos que não devem ser removidos
    'notRemoveGroups' => env('NOT_REMOVE_GROUPS', 'LOCAL_ADMIN,STI'),

    # Sincronizar grupos com replicado
    'syncGroupsWithReplicado' => env('SYNC_GROUPS_WITH_REPLICADO', 'yes'),

    # No login ou na sincronização remover todos grupos, excetos
    # os que estão em notRemoveGroups.
    'removeAllGroups' => env('REMOVE_ALL_GROUPS','no'),

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
    # username, employeeNumber, physicalDeliveryOfficeName
    # vai ser aplicado strtolower então o case não importa
    'campoCodpes' => env('CAMPO_CODPES','username'),

    # Tipo padrão de senha
    # data_nascimento, random
    'senhaPadrao' => env('SENHA_PADRAO','data_nascimento'),

    # Complexidade de senha
    'senhaComplexidade' => env('SENHA_COMPLEXIDADE', 'Mínimo de 8 caracteres.,Letras e números.,Ao menos um caracter especial.'),

    # Forçar trocar senha no pŕoximo login do windows
    # se o login com AD é usado em outros sistemas, como aplicações web
    # ou em terminais com linux deixar essa opção como 0 (falsa)
    # pois o usuário fica travado e não consegue logar em nada a não ser nos windows.
    # Por default está 1 (true) pois assim estava no Ldap/User.php
    # Válido para criação de contassincronizadas
    'obrigaTrocarSenhaNoWindows' => env('OBRIGA_TROCAR_SENHA_NO_WINDOWS', 1),

    # Sincronizar grupos usando nome por extenso ou somente siglas?
    # extenso, siglas
    'tipoNomesGrupos' => env('TIPO_NOMES_GRUPOS', 'extenso'),

    # Curso de graduação x Habilitação x Setor (Departamento de ensino)
    # Se não estiver configurada no env o método setorAluno será utilizado
    'grCursoSetor' => (env('CUR_HAB_SET')) ? json_decode(file_get_contents(env('CUR_HAB_SET')), true) : [],

    # 0 não mostra foto (nem foto fake), 1 mostra foto
    'mostrarFoto' => env('MOSTRAR_FOTO', 0),

    # Prefixo do username
    'prefixUsername' => env('PREFIX_USERNAME', ''),
];

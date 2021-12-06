<?php

return [
    # Footer theme
    'footer' => env('FOOTER', false),

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
];

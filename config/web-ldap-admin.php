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

];

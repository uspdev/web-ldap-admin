<?php

return [
    'title' => env('APP_NAME'),
    'dashboard_url' => '/',
    'logout_method' => 'GET',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'menu' => [
        [
            'text' => 'Minha Conta',
            'url'  => '/ldapusers/my',
            'can'  => 'logado',
        ],
        [
            'text' => 'Usuários Ldap',
            'url'  => '/ldapusers',
            'can'  => 'admin',
        ],
        [
            'text' => 'Configurações',
            'url'  => '/configs',
            'can'  => 'admin',
        ],
#        [
#            'text' => 'Solicitação de Administrador',
#            'url'  => '/ldapusers/solicita-admin',
#            'can'  => 'logado',
#        ],
    ],
];

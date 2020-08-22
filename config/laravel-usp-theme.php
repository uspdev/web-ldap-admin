<?php

return [
    'title' => env('APP_NAME'),
    'dashboard_url' => env('APP_URL'),
    'logout_method' => 'GET',
    'logout_url' => env('APP_URL').'/logout',
    'login_url' => env('APP_URL').'/login',
    'menu' => [
        [
            'text' => 'Minha Conta',
            'url'  => env('APP_URL').'/ldapusers/my',
            'can'  => 'logado',
        ],
        [
            'text' => 'Usuários Ldap',
            'url'  => env('APP_URL').'/ldapusers',
            'can'  => 'admin',
        ],
        [
            'text' => 'Configurações',
            'url'  => env('APP_URL').'/configs',
            'can'  => 'admin',
        ],
#        [
#            'text' => 'Solicitação de Administrador',
#            'url'  => 'ldapusers/solicita-admin',
#            'can'  => 'logado',
#        ],
    ],
];

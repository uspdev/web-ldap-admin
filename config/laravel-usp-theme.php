<?php

return [
    'title' => config('app.name'),
    'dashboard_url' => config('app.url'),
    'logout_method' => 'GET',
    'logout_url' => config('app.url') . '/logout',
    'login_url' => config('app.url') . '/login',
    'menu' => [
        [
            'text' => 'Minha Conta',
            'url' => config('app.url') . '/ldapusers/my',
            'can' => 'logado',
        ],
        [
            'text' => 'Usuários Ldap',
            'url' => config('app.url') . '/ldapusers',
            'can' => 'admin',
        ],
        [
            'text' => 'Configurações',
            'url' => config('app.url') . '/configs',
            'can' => 'admin',
        ],
#        [
        #            'text' => 'Solicitação de Administrador',
        #            'url'  => config('app.url').'ldapusers/solicita-admin',
        #            'can'  => 'logado',
        #        ],
    ],
];

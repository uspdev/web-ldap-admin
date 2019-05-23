<?php

return [

    'title' => env('APP_NAME'),
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
    ],
];

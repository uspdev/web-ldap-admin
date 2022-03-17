<?php

$menu = [
    [
        'text' => 'Minha Conta (trocar senha da rede)',
        'url' => 'ldapusers/my',
        'can' => 'user',
    ],
    [
        'key' => 'solicitaContaAdmin', # menu dinâmico solicita conta admin
    ],
    [
        'text' => 'Usuários Ldap',
        'url' => 'ldapusers',
        'can' => 'gerente',
    ],
    [
        'text' => 'Criar usuário',
        'url' => 'ldapusers/create',
        'can' => 'gerente',
    ]
];

$right_menu = [
    [
        'key' => 'web-ldap-admin',
    ],
    [
        'key' => 'senhaunica-socialite',
    ],
    [
        'text' => '<i class="fas fa-cog"></i> Sincronizar ' . env('LDAP_OU_DEFAULT'),
        'title' => 'Configurações',
        'url' => 'configs',
        'align' => 'right',
        'can' => 'admin',
    ],
];

return [
    'title' => config('app.name'),

    # USP_THEME_SKIN deve ser colocado no .env da aplicação
    'skin' => env('USP_THEME_SKIN', 'uspdev'),

    # chave da sessão. Troque em caso de colisão com outra variável de sessão.
    'session_key' => 'laravel-usp-theme',

    # usado na tag base, permite usar caminhos relativos nos menus e demais elementos html
    # na versão 1 era dashboard_url
    'app_url' => config('app.url'),

    # login e logout
    'logout_method' => 'POST',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'menu' => $menu,
    'right_menu' => $right_menu,
];

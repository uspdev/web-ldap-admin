<?php

$menu = [
    [
        'text' => 'Minha Conta',
        'url' => config('app.url') . '/ldapusers/my',
        'can' => 'user',
    ],
    [
        'key' => 'solicitaContaAdmin', # menu dinâmico solicita conta admin
    ],
    [
        'text' => 'Usuários Ldap',
        'url' => config('app.url') . '/ldapusers',
        'can' => 'admin',
    ]
];

$right_menu = [
    [
        'key' => 'senhaunica-socialite',
    ],
    [
        'text' => '<i class="fas fa-cog"></i>',
        'title' => 'Configurações',
        'url' => config('app.url') . '/configs',
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

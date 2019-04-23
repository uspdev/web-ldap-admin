<?php

return [


    'senha_unica_key' => env('SENHAUNICA_KEY', false),
    'senha_unica_secret' => env('SENHAUNICA_SECRET', false),
    'senha_unica_callback_id' => env('SENHAUNICA_CALLBACK_ID', false),

    # Unidades autorizadas
    'replicado_unidade' => env('REPLICADO_UNIDADE'),

    # Admins
    'superadmins_usernames' => env('SUPERADMINS_USERNAMES'),

];

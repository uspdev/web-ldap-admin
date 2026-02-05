<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default LDAP Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of your LDAP connections you would like to use
    | by default. This will be used when no connection name is specified
    | when executing operations on your LDAP server.
    |
    */

    'default' => env('LDAP_CONNECTION', 'default'),

    /*
    |--------------------------------------------------------------------------
    | LDAP Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many LDAP connections as you'd like.
    |
    */

    'connections' => [

        'default' => [
            /*
             |--------------------------------------------------------------------------
             | LDAP Hosts
             |--------------------------------------------------------------------------
             |
             | This is an array of hosts that your application will connect to.
             |
             */
            'hosts' => explode(' ', env('LDAP_HOSTS', '127.0.0.1')),

            /*
             |--------------------------------------------------------------------------
             | LDAP Username
             |--------------------------------------------------------------------------
             |
             | This is the distinguished name of a user that has permission to search
             | the directory. This is not a user that will be authenticated, but
             | a service account for your application to use for browsing.
             |
             */
            'username' => env('LDAP_USERNAME'),

            /*
             |--------------------------------------------------------------------------
             | LDAP Password
             |--------------------------------------------------------------------------
             |
             | The password for your service account to browse the directory.
             |
             */
            'password' => env('LDAP_PASSWORD'),

            /*
             |--------------------------------------------------------------------------
             | LDAP Port
             |--------------------------------------------------------------------------
             |
             | The port to connect to your LDAP server on.
             |
             */
            'port' => env('LDAP_PORT', 389),

            /*
             |--------------------------------------------------------------------------
             | Base Distinguished Name
             |--------------------------------------------------------------------------
             |
             | The base distinguished name to perform searches upon.
             |
             */
            'base_dn' => env('LDAP_BASE_DN', 'dc=local,dc=com'),

            /*
             |--------------------------------------------------------------------------
             | LDAP Timeout
             |--------------------------------------------------------------------------
             |
             | The timeout to use when performing LDAP operations.
             |
             */
            'timeout' => env('LDAP_TIMEOUT', 5),

            /*
             |--------------------------------------------------------------------------
             | SSL & TLS
             |--------------------------------------------------------------------------
             |
             | Whether to use SSL or TLS when connecting to your LDAP server.
             |
             */
            'use_ssl' => env('LDAP_USE_SSL', false),
            'use_tls' => env('LDAP_USE_TLS', false),

            'options' => [
                LDAP_OPT_X_TLS_REQUIRE_CERT => LDAP_OPT_X_TLS_NEVER,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Logging
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, all LDAP operations will be logged using
    | your applications default logger. This is great for debugging.
    |
    */

    'logging' => [
        'enabled' => env('LDAP_LOGGING', true),
        'channel' => env('LOG_CHANNEL', 'stack'),
    ],


    /*
    |--------------------------------------------------------------------------
    | LDAP Cache
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, all LDAP search results will be cached
    | using your applications default cache driver. This is great for
    | speeding up applications that make the same LDAP queries.
    |
    */

    'cache' => [
        'enabled' => env('LDAP_CACHE', false),
        'driver' => env('CACHE_DRIVER', 'file'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP Auth
    |--------------------------------------------------------------------------
    |
    | Here you may configure LdapRecord-Laravel authentication settings.
    |
    */
    'auth' => [
        'providers' => [
            'default' => [
                'driver' => 'eloquent',
                'model' => LdapRecord\Models\ActiveDirectory\User::class,

                'rules' => [
                    \LdapRecord\Laravel\Auth\Rule\DenyTrashed::class,
                ],
                'scopes' => [],

                'database' => [
                    'model' => App\Models\User::class,
                    'sync_passwords' => env('LDAP_PASSWORD_SYNC', false),
                    'sync_attributes' => [
                        'email' => 'mail',
                        'name' => 'cn',
                    ],
                ],

                'sync_existing' => [
                    'enabled' => true,
                    'attributes' => [
                        'email' => 'mail',
                    ],
                ],

                'fallback' => [
                    'enabled' => env('LDAP_LOGIN_FALLBACK', false),
                    'username' => 'email',
                ],
            ],
        ],
    ],
];
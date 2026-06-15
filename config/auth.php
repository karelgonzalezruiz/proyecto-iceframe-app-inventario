<?php

return [
    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'usuarios'),
    ],

    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'usuarios',
        ],
    ],

    // El proveedor apunta al modelo Usuario (tabla `usuarios`), NO a `users`.
    'providers' => [
        'usuarios' => [
            'driver' => 'eloquent',
            'model'  => App\Models\Usuario::class,
        ],
    ],

    'passwords' => [
        'usuarios' => [
            'provider' => 'usuarios',
            'table'    => 'password_reset_tokens',
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];

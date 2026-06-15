<?php

return [
    'name'             => env('APP_NAME', 'IceFrame'),
    'env'              => env('APP_ENV', 'production'),
    'debug'            => (bool) env('APP_DEBUG', false),
    'url'              => env('APP_URL', 'http://localhost'),
    'timezone'         => env('APP_TIMEZONE', 'UTC'),
    'locale'           => env('APP_LOCALE', 'es'),
    'fallback_locale'  => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale'     => env('APP_FAKER_LOCALE', 'es_ES'),
    'cipher'           => 'AES-256-CBC',
    'key'              => env('APP_KEY'),
    'previous_keys'    => [],
    'maintenance'      => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store'  => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    // URL del módulo de reportes (Contenedor B del Estudiante 2). Vive en otro
    // contenedor/máquina, accesible por la red Tailscale. El botón "Ver reportes"
    // del dashboard apunta aquí. Configurable por entorno.
    'reportes_url'     => env('REPORTES_URL', ''),
];

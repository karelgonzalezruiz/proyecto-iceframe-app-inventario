<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [

        // Conexión principal a PostgreSQL de IceFrame.
        // La aplicación se conecta por Tailscale: DB_HOST=IP_TAILSCALE, DB_PORT=5433.
        // db/init.sql (Parte 2) es la fuente de verdad del esquema; Laravel NO migra
        // tablas del negocio.
        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DB_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE', 'iceframe'),
            'username'       => env('DB_USERNAME', 'iceframe'),
            'password'       => env('DB_PASSWORD', ''),
            'charset'        => env('DB_CHARSET', 'utf8'),
            'prefix'         => '',
            'prefix_indexes' => true,
            'search_path'    => 'public',
            'sslmode'        => env('DB_SSLMODE', 'prefer'),
            // Conexión persistente: con el servidor embebido (un proceso de larga
            // vida) la conexión TCP a la BD remota por Tailscale se reutiliza entre
            // peticiones, evitando rehacer el handshake en cada navegación.
            // Apagable con DB_PERSISTENT=false en el .env.
            'options'        => filter_var(env('DB_PERSISTENT', true), FILTER_VALIDATE_BOOLEAN)
                ? [PDO::ATTR_PERSISTENT => true]
                : [],
        ],

    ],

    'migrations' => [
        'table'                  => 'migrations',
        'update_date_on_publish' => true,
    ],

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'default' => [
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port'     => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
    ],
];

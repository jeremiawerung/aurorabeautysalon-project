<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    'guards' => [
        // Guard default untuk User
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // Guard untuk Admin
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // Guard untuk Pelanggan
        'pelanggan' => [
            'driver' => 'session',
            'provider' => 'pelanggans',
        ],
    ],

    'providers' => [
        // Provider default
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // Provider untuk Admin
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        // Provider untuk Pelanggan (nama harus sama dengan di guards!)
        'pelanggans' => [
            'driver' => 'eloquent',
            'model' => App\Models\Pelanggan::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
        'pelanggans' => [
            'provider' => 'pelanggans',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];

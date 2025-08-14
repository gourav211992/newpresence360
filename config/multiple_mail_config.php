<?php

return [
    'mailers' => [
        'noreply_staqo' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        'noreply_p360' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_1'),
            'port' => env('MAIL_PORT_1'),
            'encryption' => env('MAIL_ENCRYPTION_1'),
            'username' => env('MAIL_USERNAME_1'),
            'password' => env('MAIL_PASSWORD_1'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        'notify_p360' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_2'),
            'port' => env('MAIL_PORT_2'),
            'encryption' => env('MAIL_ENCRYPTION_2'),
            'username' => env('MAIL_USERNAME_2'),
            'password' => env('MAIL_PASSWORD_2'),
            'timeout' => null,
            'auth_mode' => null,
        ],
        'alerts_p360' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST_3'),
            'port' => env('MAIL_PORT_3'),
            'encryption' => env('MAIL_ENCRYPTION_3'),
            'username' => env('MAIL_USERNAME_3'),
            'password' => env('MAIL_PASSWORD_3'),
            'timeout' => null,
            'auth_mode' => null,
        ],
    ],
];

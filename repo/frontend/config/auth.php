<?php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => null,
    ],
    'guards' => [
        'web' => [
            'driver' => 'session-api',
            'provider' => 'api-users',
        ],
    ],
    'providers' => [
        'api-users' => [
            'driver' => 'session-api',
        ],
    ],
];

<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:8000',
        'http://localhost:8000',
        'http://192.168.31.6',
        'http://192.168.31.6:8000',
        'http://192.168.31.6:5173',
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];




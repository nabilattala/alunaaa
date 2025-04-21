<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'products/export'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'http://localhost:3032', 
        'http://localhost:8000',
        'http://127.0.0.1:8000'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization'],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => true,

];
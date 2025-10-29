<?php

return [
    'app' => [
        'name' => 'App',
        'env' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => ($_ENV['APP_DEBUG'] ?? 'true') === 'true',
        'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    ],
    
    'jwt' => [
        'secret_key' => $_ENV['JWT_SECRET'] ?? 'change_this_secret_key_in_production',
        'expiration' => (int) ($_ENV['JWT_EXPIRATION'] ?? 300), // 5 minutes par défaut
        'algorithm' => 'HS256',
    ],
    
    'session' => [
        'name' => 'App_session',
        'lifetime' => 7200, // 2 heures
    ],
];
<?php

return [
    'driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
    'name' => $_ENV['DB_NAME'] ?? 'App',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'collation' => $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci',
];
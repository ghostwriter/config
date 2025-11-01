<?php

declare(strict_types=1);

return [
    'driver' => 'pdo_mysql',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
    'database' => $_ENV['DB_DATABASE'] ?? 'database',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_ROOT_PASSWORD'] ?? 'secret',
    'port' => $_ENV['DB_PORT'] ?? '3306',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

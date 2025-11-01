<?php

declare(strict_types=1);

return [
    'driver' => 'pdo_pgsql',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
    'url' => $_ENV['DB_URL'] ?? '',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_DATABASE'] ?? 'database',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_ROOT_PASSWORD'] ?? 'secret',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'prefix' => '',
    'options' => [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

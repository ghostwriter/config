<?php

declare(strict_types=1);

use Ghostwriter\Config\Interface\ConfigInterface;
use Ghostwriter\Config\Interface\ConfigProviderInterface;

return new class implements ConfigProviderInterface
{
    public function __invoke(ConfigInterface $config): void
    {
        $config->set('pgsql', [
            'driver' => 'pdo_pgsql',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8',
            'database' => $_ENV['DB_DATABASE'] ?? 'ghostwriter',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'password' => $_ENV['DB_ROOT_PASSWORD'] ?? 'toor',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'prefix' => '',
            'url' => $_ENV['DB_URL'] ?? '',
            'user' => $_ENV['DB_USER'] ?? 'root',
        ]);
    }
};

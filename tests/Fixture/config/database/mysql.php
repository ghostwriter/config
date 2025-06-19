<?php

declare(strict_types=1);

use Ghostwriter\Config\ConfigInterface;
use Ghostwriter\Config\ConfigProviderInterface;

return new class implements ConfigProviderInterface
{
    public function __invoke(ConfigInterface $config): void
    {
        $config->merge([
            'driver' => 'pdo_mysql',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
            'database' => $_ENV['DB_DATABASE'] ?? 'ghostwriter',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'user' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_ROOT_PASSWORD'] ?? 'toor',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ]);
    }

//        $config->set('driver', 'pdo_mysql');
//        $config->set('charset', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
//        $config->set('database', $_ENV['DB_DATABASE'] ?? 'ghostwriter');
//        $config->set('host', $_ENV['DB_HOST'] ?? 'localhost');
//        $config->set('user', $_ENV['DB_USER'] ?? 'root');
//        $config->set('password', $_ENV['DB_ROOT_PASSWORD'] ?? 'toor');
//        $config->set('port', $_ENV['DB_PORT'] ?? '3306');
//        $config->set('collation', 'utf8mb4_unicode_ci');
//        $config->set('prefix', '');
//        $config->set('options', [
//            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
//            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//            PDO::ATTR_EMULATE_PREPARES => false,
//        ]);
//        $config->set('url', $_ENV['DB_URL'] ?? '');
//    }
};

<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Interface;

interface ConfigFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config = []): ConfigInterface;

    public function createFromDirectory(string $configDirectory): ConfigInterface;

    public function createFromFile(string $configFile): ConfigInterface;
}

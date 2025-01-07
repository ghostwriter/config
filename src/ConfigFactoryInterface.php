<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

interface ConfigFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config = []): ConfigInterface;

    public function createFromDirectory(string $directory): ConfigInterface;

    public function createFromFile(string $file): ConfigInterface;
}

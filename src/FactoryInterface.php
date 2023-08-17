<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Exception\ConfigFileNotFoundException;

interface FactoryInterface
{
    /**
     * @template TCreate
     *
     * @param array<string,TCreate>|non-empty-array<string,TCreate> $options
     */
    public function create(array $options = []): ConfigInterface;

    /**
     * @template TCreateFromPath
     *
     * @throws ConfigFileNotFoundException
     */
    public function createFromPath(string $path, ?string $key = null): ConfigInterface;
}

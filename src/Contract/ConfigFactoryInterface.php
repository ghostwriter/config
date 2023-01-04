<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Contract;

interface ConfigFactoryInterface
{
    public function create(array $options): ConfigInterface;

    public function requirePath(string $path, ?string $root = null): ConfigInterface;
}

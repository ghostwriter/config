<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Interface;

interface ConfigProviderInterface
{
    public function __invoke(ConfigInterface $config): void;
}

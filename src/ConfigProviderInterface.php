<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

interface ConfigProviderInterface
{
    public function __invoke(ConfigInterface $config): void;
}

<?php

declare(strict_types=1);

use Ghostwriter\Config\Interface\ConfigInterface;
use Ghostwriter\Config\Interface\ConfigProviderInterface;

return new class implements ConfigProviderInterface
{
    public function __invoke(ConfigInterface $config): void
    {
        $config->set('ci', ['foo' => 'bar']);
        $config->set('type', 'ci');
    }
};

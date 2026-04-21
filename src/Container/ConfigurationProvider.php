<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\BuilderInterface;
use Ghostwriter\Container\Service\Provider\AbstractProvider;
use Override;
use Throwable;

/**
 * @see ConfigurationProviderTest
 */
final class ConfigurationProvider extends AbstractProvider
{
    /** @throws Throwable */
    #[Override]
    public function register(BuilderInterface $builder): void
    {
        $builder->alias(ConfigurationInterface::class, Configuration::class);
    }
}

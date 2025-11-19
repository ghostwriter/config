<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use Override;
use Throwable;

/**
 * @see ConfigurationDefinitionTest
 */
final readonly class ConfigurationDefinition implements DefinitionInterface
{
    /** @throws Throwable */
    #[Override]
    public function __invoke(ContainerInterface $container): void
    {
        $container->alias(Configuration::class, ConfigurationInterface::class);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Container\ConfigurationDefinition;
use Ghostwriter\Config\Container\ConfigurationExtension;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\DefinitionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

use function is_a;

#[CoversClass(ConfigurationDefinition::class)]
final class ConfigurationDefinitionTest extends AbstractTestCase
{
    public function testDefinition(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::once())
            ->method('alias')
            ->with(Configuration::class, ConfigurationInterface::class);

        $container
            ->expects(self::once())
            ->method('extend')
            ->with(ConfigurationInterface::class, ConfigurationExtension::class)
            ->seal();

        (new ConfigurationDefinition())($container);
    }

    public function testInstanceOfDefinitionInterface(): void
    {
        self::assertTrue(is_a(ConfigurationDefinition::class, DefinitionInterface::class, true));
    }
}

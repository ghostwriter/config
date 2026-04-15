<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Container\ConfigurationExtension;
use Ghostwriter\Config\Container\ConfigurationProvider;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

use function is_a;

#[CoversClass(ConfigurationProvider::class)]
final class ConfigurationProviderTest extends AbstractTestCase
{
    public function testConfigurationProviderRegister(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::once())
            ->method('alias')
            ->with(ConfigurationInterface::class, Configuration::class);

        $container
            ->expects(self::once())
            ->method('extend')
            ->with(ConfigurationInterface::class, ConfigurationExtension::class)
            ->seal();

        (new ConfigurationProvider())->register($container);
    }

    public function testInstanceOfProviderInterface(): void
    {
        self::assertTrue(is_a(ConfigurationProvider::class, ProviderInterface::class, true));
    }
}

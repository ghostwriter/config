<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationKeyMustBeStringException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(Configuration::class)]
#[CoversClass(ConfigurationKeyMustBeStringException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationKeyMustBeStringExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testMergeThrowsInvalidConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeStringException::class);

        Configuration::new()->merge([true]);
    }

    /** @throws Throwable */
    public function testNewThrowsInvalidConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeStringException::class);

        Configuration::new([true]);
    }
}

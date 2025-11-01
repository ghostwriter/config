<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\InvalidConfigurationValueException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use stdClass;
use Tests\Unit\AbstractTestCase;
use Throwable;

use function mb_strtoupper;

#[CoversClass(Configuration::class)]
#[CoversClass(InvalidConfigurationValueException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class InvalidConfigurationValueExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testItCanSetAndRetrieveAClosure(): void
    {
        $configuration = Configuration::new();

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);
        $this->expectExceptionMessage(
            'Invalid configuration value for key "all-caps". Expected: array, null, or scalar (bool, float, int, string). Received: Closure.'
        );

        $configuration->set('all-caps', static fn (string $foo): string => mb_strtoupper($foo));
    }

    /** @throws Throwable */
    public function testItCanSetAndRetrieveStdClass(): void
    {
        $configuration = Configuration::new();

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);
        $this->expectExceptionMessage(
            'Invalid configuration value for key "nested.app.all-caps". Expected: array, null, or scalar (bool, float, int, string). Received: stdClass.',
        );

        $configuration->set('nested.app.all-caps', new stdClass());
    }

    /** @throws Throwable */
    public function testSetWithObjectValueThrowsInvalidConfigurationValueException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);
        Configuration::new()->set('obj', new stdClass());
    }

    /** @throws Throwable */
    public function testWrap(): void
    {
        $configuration = Configuration::new([
            'key' => 'value',
        ]);

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);

        $this->expectExceptionMessage('Cannot wrap configuration key "key". Expected an array value, received string.');

        $configuration->wrap('key');
    }

    /** @throws Throwable */
    public function testWrapWithNonArrayValueThrowsException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);
        Configuration::new([
            'key' => 'scalar-value',
        ])->wrap('key');
    }

    /** @throws Throwable */
    public function testWrapWithNullValueThrowsException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidConfigurationValueException::class);
        Configuration::new([
            'nullKey' => null,
        ])->wrap('nullKey');
    }
}

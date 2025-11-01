<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\InvalidDotNotationConfigurationKeyException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(Configuration::class)]
#[CoversClass(InvalidDotNotationConfigurationKeyException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class InvalidDotNotationConfigurationKeyExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testAppendThrowsInvalidDotNotationConfigKeyException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);

        Configuration::new()->append('...', 'baz');
    }

    /** @throws Throwable */
    public function testGetDots(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);

        self::assertSame('baz', Configuration::new([
            'foo' => 'bar',
        ])->get('...', 'baz'));
    }

    /** @throws Throwable */
    public function testInstanceOfConfigExceptionInterface(): void
    {
        self::assertInstanceOf(
            ConfigurationExceptionInterface::class,
            new InvalidDotNotationConfigurationKeyException('...')
        );
    }

    /** @throws Throwable */
    public function testNewThrowsInvalidDotNotationConfigKeyException(): void
    {
        $key = '...';

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);

        $this->expectExceptionMessage($key);

        Configuration::new([
            $key => 'value',
        ]);
    }

    /** @throws Throwable */
    public function testSetThrowsInvalidDotNotationConfigKeyException(): void
    {
        $key = '..invalid..';

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);

        $this->expectExceptionMessage($key);

        Configuration::new()->set($key, 'value');
    }

    /** @throws Throwable */
    public function testSetWithEmptySegmentInKeyThrowsInvalidDotNotationException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);
        Configuration::new()->set('a..b', 'value');
    }

    /**
     * Ensures an invalid UTF-8 key causes the internal splitter to yield an empty array,
     * which then triggers a dot-notation validation exception when applying the operation.
     *
     * @throws Throwable
     */
    public function testSetWithInvalidUtf8KeyThrowsInvalidDotNotationException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(InvalidDotNotationConfigurationKeyException::class);

        // Craft a key containing invalid UTF-8 bytes. With the 'u' modifier in the regex,
        // preg_split will fail and return false, which our code converts to an empty array.
        // This leads to splitIntoHeadAndLastSegment throwing an InvalidDotNotationConfigurationKeyException.
        $invalidUtf8Key = "a\xFFb";

        Configuration::new()->set($invalidUtf8Key, 'value');
    }
}

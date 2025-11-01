<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\InvalidConfigurationKeyException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(Configuration::class)]
#[CoversClass(InvalidConfigurationKeyException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class InvalidConfigurationKeyExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testAppendOnDeepChainWithIntermediateScalarPromotesAndAppends(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => [
                'b' => 'scalar',
            ],
        ]);

        $configuration->append('a.b.c.d', 'x');

        self::assertSame([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => ['x'],
                    ],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testAppendOnNestedKeyWhenParentIsScalar(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'parent' => 'scalar',
        ]);
        $configuration->append('parent.child', 'value');
        self::assertSame([
            'parent' => [
                'child' => ['value'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testAppendPromotesScalarParentToArrayWhenAppendingNestedKey(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => 'scalar',
        ]);
        $configuration->append('a.b', 'val');
        self::assertSame([
            'a' => [
                'b' => ['val'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testPrependOnDeepChainWithIntermediateScalarPromotesAndPrepends(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => [
                'b' => 'scalar',
            ],
        ]);

        $configuration->prepend('a.b.c.d', 'x');

        self::assertSame([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => ['x'],
                    ],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testPrependOnNestedKeyWhenParentIsScalar(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'parent' => 'scalar',
        ]);
        $configuration->prepend('parent.child', 'value');
        self::assertSame([
            'parent' => [
                'child' => ['value'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testPrependPromotesScalarParentToArrayWhenPrependingNestedKey(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => 'scalar',
        ]);
        $configuration->prepend('a.b', 'val');
        self::assertSame([
            'a' => [
                'b' => ['val'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetOnNestedKeyWhenIntermediateIsScalarPromotesToArray(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => [
                'b' => 'scalar',
            ],
        ]);
        $configuration->set('a.b.c', 'value');
        self::assertSame([
            'a' => [
                'b' => [
                    'c' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetOnNestedKeyWhenParentIsScalarPromotesToArray(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'parent' => 'scalar',
        ]);
        $configuration->set('parent.child', 'value');
        self::assertSame([
            'parent' => [
                'child' => 'value',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetPromotesScalarParentToArrayWhenSettingNestedKey(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationKeyException::class);

        $configuration = Configuration::new([
            'a' => 'scalar',
        ]);
        $configuration->set('a.b', 'val');
        self::assertSame([
            'a' => [
                'b' => 'val',
            ],
        ], $configuration->toArray());
    }
}

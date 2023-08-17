<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit;

use Generator;
use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\ExceptionInterface;
use Ghostwriter\Config\FactoryInterface;
use Ghostwriter\Config\Tests\Unit\Traits\FixtureTrait;


use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Config::class)]
#[CoversClass(ConfigFactory::class)]
final class ConfigFactoryTest extends TestCase
{
    use FixtureTrait;

    /**
     * @return Generator<string, array<string>>
     */
    public static function invalidPaths(): Generator
    {
        yield 'invalid' => [tempnam(sys_get_temp_dir(), 'invalid-key'), 'invalid-key'];
    }

    public function testConstruct(): void
    {
        Assert::assertInstanceOf(FactoryInterface::class, new ConfigFactory());
    }

    /**
     * @template T
     *
     * @param array<string,T> $options
     */
    #[DataProvider('validOptions')]
    public function testCreate(array $options): void
    {
        $configFactory = new ConfigFactory();

        $config = $configFactory->create($options);

        Assert::assertSame($options, $config->toArray());
    }

    #[DataProvider('invalidPaths')]
    public function testRequireInvalidPaths(string $path, string $key): void
    {
        $configFactory = new ConfigFactory();

        $this->expectException(ExceptionInterface::class);
        $this->expectException(InvalidConfigFileException::class);
        $this->expectExceptionMessage($path);

        $configFactory->createFromPath($path, $key);
    }

    #[DataProvider('validPaths')]
    public function testRequirePath(string $path, string $key): void
    {
        $configFactory = new ConfigFactory();

        $config = $configFactory->createFromPath($path, $key);

        Assert::assertArrayHasKey($key, $config);

        Assert::assertTrue(
            is_file($path)
        );

        /** @var array $options */
        $options = require $path;

        Assert::assertSame([
            $key => $options,
        ], $config->wrap($key)->toArray());
    }

    /**
     * @return Generator<string,array<array-key,array>>
     */
    public static function validOptions(): Generator
    {
        yield from [
            'empty' => [[]],
            'string' => [[
                'string-key' => 'string-value',
            ]],
            'null' => [[
                'null' => null,
            ]],
            'closure' => [[
                'closure' => static fn (): string => 'closure',
            ]],
        ];
    }

    /**
     * @return Generator<string,array<string>>
     */
    public static function validPaths(): Generator
    {
        yield from [
            'local' => [self::fixture('local'), 'local-key'],
            'testing' => [self::fixture('testing'), 'testing-key'],
        ];
    }
}

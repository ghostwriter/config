<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit;

use Generator;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Contract\ConfigFactoryInterface;
use Ghostwriter\Config\Contract\Exception\ConfigExceptionInterface;
use Ghostwriter\Config\Tests\Unit\Traits\FixtureTrait;


use PHPUnit\Framework\TestCase;

/**
 * @covers \Ghostwriter\Config\ConfigFactory
 *
 * @internal
 *
 * @small
 */
final class ConfigFactoryTest extends TestCase
{
    use FixtureTrait;

    public function testConstruct(): void
    {
        self::assertInstanceOf(ConfigFactoryInterface::class, new ConfigFactory());
    }

    /**
     * @return Generator<string,array<array-key,array>>
     */
    public function validOptions(): Generator
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
     * @covers \Ghostwriter\Config\Config::__construct
     * @covers \Ghostwriter\Config\Config::toArray
     * @covers \Ghostwriter\Config\ConfigFactory::create
     *
     * @dataProvider validOptions
     */
    public function testCreate(array $options): void
    {
        $configFactory = new ConfigFactory();

        $config = $configFactory->create($options);

        self::assertSame($options, $config->toArray());
    }

    /**
     * @return Generator<string,array<string>>
     */
    public function validPaths(): Generator
    {
        yield from [
            'local' => [$this->fixture('local'), 'local-key'],
            'testing' => [$this->fixture('testing'), 'testing-key'],
        ];
    }

    /**
     * @covers \Ghostwriter\Config\Config::__construct
     * @covers \Ghostwriter\Config\Config::get
     * @covers \Ghostwriter\Config\Config::has
     * @covers \Ghostwriter\Config\Config::offsetExists
     * @covers \Ghostwriter\Config\Config::wrap
     * @covers \Ghostwriter\Config\Config::toArray
     * @covers \Ghostwriter\Config\ConfigFactory::createFromPath
     *
     * @dataProvider validPaths
     *
     * @psalm-suppress UnresolvableInclude
     */
    public function testRequirePath(string $path, string $key): void
    {
        $configFactory = new ConfigFactory();

        $config = $configFactory->createFromPath($path, $key);

        self::assertArrayHasKey($key, $config);

        /** @var array $options */
        $options = require $path;
        self::assertSame($options, $config->wrap($key)->toArray());
    }

    /**
     * @return Generator<string, array<string>>
     */
    public function invalidPaths(): Generator
    {
        yield 'invalid' => [tempnam(sys_get_temp_dir(), 'invalid-key'), 'invalid-key'];
    }

    /**
     * @covers \Ghostwriter\Config\Config::__construct
     * @covers \Ghostwriter\Config\Config::get
     * @covers \Ghostwriter\Config\Config::wrap
     * @covers \Ghostwriter\Config\Config::toArray
     * @covers \Ghostwriter\Config\ConfigFactory::raiseInvalidPathException
     * @covers \Ghostwriter\Config\ConfigFactory::createFromPath
     *
     * @dataProvider invalidPaths
     */
    public function testRequireInvalidPaths(string $path, string $key): void
    {
        $configFactory = new ConfigFactory();

        $this->expectException(ConfigExceptionInterface::class);
        $this->expectExceptionMessage(sprintf('Invalid config path: "%s".', $path));

        $configFactory->createFromPath($path, $key);
    }
}

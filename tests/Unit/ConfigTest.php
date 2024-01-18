<?php

declare(strict_types=1);

namespace Ghostwriter\ConfigTests\Unit;

use Closure;
use EmptyIterator;
use Generator;
use Ghostwriter\Config\Config;
use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\Interface\ConfigExceptionInterface;
use Ghostwriter\Config\Interface\ConfigInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFixedArray;
use stdClass;
use Throwable;
use Traversable;
use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;
use function count;
use function dirname;
use function is_callable;
use function is_file;
use function is_iterable;
use function iterator_count;
use function mb_strtolower;
use function mb_strtoupper;
use function realpath;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;

#[CoversClass(Config::class)]
final class ConfigTest extends TestCase
{
    /**
     * @template TMixed
     *
     * @param array<string,TMixed> $options
     */
    public function createConfig(array $options = []): Config
    {
        return new Config($options);
    }

    public function testAdd(): void
    {
        $config = Config::new(self::fixture('dev'));

        $expected = [
            ...$config->toArray(),
            'key' => 'value',
        ];

        $config->set('key', 'value');

        self::assertSame($expected, $config->toArray());
        self::assertTrue($config->has('key'));
        self::assertSame('value', $config->get('key'));
    }

    public function testAddAndGetUsingDotNotation(): void
    {
        $config = new Config([
            'app' => 'key',
        ]);

        $config->set('foo.bar.baz', 'foo-bar-baz');

        self::assertSame([
            'app' => 'key',
            'foo' => [
                'bar' => [
                    'baz' => 'foo-bar-baz',
                ],
            ],
        ], $config->toArray());
        self::assertSame([
            'bar' => [
                'baz' => 'foo-bar-baz',
            ],
        ], $config->get('foo'));
        self::assertSame([
            'baz' => 'foo-bar-baz',
        ], $config->get('foo.bar'));
        self::assertSame('foo-bar-baz', $config->get('foo.bar.baz'));
    }

    #[DataProvider('setValidOptionProvider')]
    public function testAddValidOption(string $key, mixed $value): void
    {
        $config = new Config();

        $config->set($key, $value);

        /** @var null|mixed $actual */
        $actual = $config->get($key);

        self::assertSame($value, $actual);

        if (is_callable($value)) {
            /** @var callable $actual */
            self::assertSame($value(), $actual());
        }

        self::assertSame([
            $key => $value,
        ], $config->toArray());

        if (! is_iterable($value)) {
            return;
        }

        $expectedCount = $value instanceof Traversable
            ? iterator_count($value)
            : count($value);
        /** @var iterable $actual */
        self::assertCount($expectedCount, $actual);
    }

    public function testDefault(): void
    {
        $config = new Config([
            'config' => [
                'type' => 'local',
                'testing' => [
                    'foo' => 'bar',
                ],
                'local' => [
                    'foo' => 'baz',
                ],
            ],
        ]);

        self::assertSame('local', $config->get('config.type', 'remote'));
    }

    public function testGet(): void
    {
        $config = $this->createConfig([
            'foo' => 'bar',
        ]);

        self::assertSame('bar', $config->get('foo'));
    }

    public function testGetExistingWithDefault(): void
    {
        $config = $this->createConfig([
            'exist' => 'exist',
        ]);

        self::assertSame('exist', $config->get('exist', 'default'));
    }

    public function testGetNonExistingWithDefault(): void
    {
        $config = $this->createConfig();

        self::assertSame('default', $config->get('not-exist', 'default'));
    }

    public function testHasIsFalse(): void
    {
        $config = $this->createConfig();

        self::assertFalse($config->has('not-exist'));
        self::assertFalse($config->has('foo.not-exist'));
    }

    public function testHasIsTrue(): void
    {
        $config = $this->createConfig([
            'foo' => 'bar',
        ]);

        self::assertTrue($config->has('foo'));
    }

    public function testInstantiable(): void
    {
        $config = $this->createConfig();

        self::assertInstanceOf(Config::class, $config);
        self::assertInstanceOf(ConfigInterface::class, $config);
    }

    public function testIsEmpty(): void
    {
        $config = $this->createConfig();

        self::assertEmpty($config->toArray());
        self::assertNull($config->get('nested.non-existent'));
    }

    public function testItCanBeReturnedAsAnArray(): void
    {
        $expected = [
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ];

        $config = new Config($expected);

        self::assertSame($expected, $config->toArray());
    }

    public function testItCanSetAndRetrieveAClosure(): void
    {
        $config = $this->createConfig();

        $config->set('all-caps', static fn (string $foo): string => mb_strtoupper($foo));

        /** @var ?callable $callable */
        $callable = $config->get('all-caps');

        self::assertIsCallable($callable);
        self::assertInstanceOf(Closure::class, $callable);
        self::assertSame('STRING', $callable('string'));
    }

    public function testItCanSplitIntoASubObject(): void
    {
        $config = new Config([
            'foo' => 'foo1',
            'bar' => [
                'baz' => 'barBaz',
            ],
        ]);

        //        $bar = $config->wrap('bar');

        self::assertSame('barBaz', $config->get('bar.baz'));
        //        self::assertNull($bar->get('foo'));
    }

    public function testItCanUnsetAnOption(): void
    {
        $config = $this->createConfig([
            'foo' => [
                'bar' => 'foobar',
                'baz' => 'foobaz',
            ],
            'bar' => 'bar',
        ]);

        $config->remove('foo.baz');
        $config->remove('bar');

        self::assertTrue($config->has('foo.bar'));
        self::assertFalse($config->has('foo.baz'));
        self::assertFalse($config->has('bar'));
    }

    /**
     * @param class-string<Throwable> $exception
     */
    #[DataProvider('invalidPaths')]
    public function testRequireInvalidPaths(string $path, string $key, string $exception): void
    {
        $this->expectException(ConfigExceptionInterface::class);

        $this->expectException($exception);

        $this->expectExceptionMessage($path);

        Config::new($path, $key);
    }

    #[DataProvider('validPaths')]
    public function testRequirePath(string $path, string $key): void
    {
        $config = Config::new($path, $key);

        self::assertTrue($config->has($key));

        self::assertFileExists($path);

        if (is_file($path)) {
            /** @var array $options */
            $options = require $path;

            self::assertSame($options, $config->get($key));
        }
    }

    public function testReturnsDefaultConfigOptionValueIfConfigOptionDoesNotExist(): void
    {
        $config = $this->createConfig();

        self::assertNull($config->get('does-not-exist'));
        self::assertTrue($config->get('does-not-exist', true));
        self::assertFalse($config->get('does-not-exist', false));
        self::assertInstanceOf(stdClass::class, $config->get('does-not-exist', new stdClass()));
    }

    public function testReturnsFalseIfKeyDoesNotExist(): void
    {
        $config = $this->createConfig();

        self::assertFalse($config->has('does-not-exist'));
    }

    public function testReturnsNullIfConfigOptionDoesNotExist(): void
    {
        $config = $this->createConfig();

        self::assertNull($config->get('does-not-exist'));
    }

    public function testReturnsTrueIfHas(): void
    {
        $config = new Config([
            'has' => 'some-item',
        ]);

        self::assertTrue($config->has('has'));
    }

    public function testReturnsTrueIfKeyExist(): void
    {
        $config = new Config([
            'foo' => [
                'bar' => 'foobar',
            ],
        ]);

        self::assertTrue($config->has('foo.bar'));
    }

    public function testReturnsTrueIfKeyIsBooleanFalse(): void
    {
        $config = new Config([
            'false' => false,
        ]);

        self::assertTrue($config->has('false'));
    }

    public function testSet(): void
    {
        $config = $this->createConfig();
        $config->set('key', 'value');
        self::assertSame('value', $config->get('key'));
    }

    public function testToArray(): void
    {
        $configurations = [
            'foo' => 'bar',
        ];

        $config = new Config($configurations);

        self::assertSame($configurations, $config->toArray());
    }

    public static function fixture(string $path): string
    {
        $realpath = realpath(sprintf('%s/Fixture/config.%s.php', dirname(__DIR__, 1), mb_strtolower($path)));

        if ($realpath === false) {
            throw new ConfigFileNotFoundException($path);
        }

        return $realpath;
    }

    public static function invalidPaths(): Generator
    {
        yield 'invalid-file-contents' => [
            tempnam(sys_get_temp_dir(), 'invalid-key'),
            'not-an-array',
            InvalidConfigFileException::class,
        ];
        yield 'invalid-file-path' => ['invalid/file/path', 'not-a-file', ConfigFileNotFoundException::class];
    }

    /**
     * @return Generator<string,array{string,mixed}>
     */
    public static function setValidOptionProvider(): Generator
    {
        yield 'null' => ['null', null];
        yield 'EmptyIterator' => ['EmptyIterator', new EmptyIterator()];
        yield 'SplFixedArray' => ['SplFixedArray', new SplFixedArray()];
        yield 'string' => ['key', stdClass::class];
        yield 'float' => ['float', PHP_FLOAT_MAX];
        yield 'int' => ['int', PHP_INT_MAX];
        yield 'object' => ['object', new stdClass()];
        yield 'Closure' => [
            'Closure',
            static fn (): bool => true,
        ];

        yield 'array' => [
            'array', [
                'key' => 'value',
            ]];

        yield 'nested-array' => [
            'nested-array', [
                'nested' => [
                    'array' => [
                        'key' => 'value',
                    ],
                ],
            ]];
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

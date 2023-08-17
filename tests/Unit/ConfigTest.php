<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit;

use Closure;
use EmptyIterator;
use Generator;
use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\ConfigInterface;
use Ghostwriter\Config\Tests\Unit\Traits\FixtureTrait;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SplFixedArray;
use stdClass;


use Traversable;
use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;

#[CoversClass(Config::class)]
#[CoversClass(ConfigFactory::class)]
final class ConfigTest extends TestCase
{
    use FixtureTrait;

    private ConfigInterface $config;

    private array $configuration = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = [
            'BLM' => '#BlackLivesMatter',
            'bool' => [
                'true' => true,
                'false' => false,
            ],
            'int' => PHP_INT_MAX,
            'float' => PHP_FLOAT_MAX,
            'null' => null,
            'array' => ['foo', 'bar'],
            'foo' => 'bar',
            'foo.bar' => 'foo-bar',
            'foobar' => [
                'bar.baz' => 'foo-bar-baz',
            ],
            'bar' => 'baz',
            'baz' => 'bat',
            'associate' => [
                'x' => 'xxx',
                'y' => 'yyy',
            ],
            'x' => [
                'z' => 'zoo',
            ],
        ];

        $this->setUpConfig($this->configuration);
    }

    /**
     * @param array<array-key,mixed> $options
     */
    public function setUpConfig(array $options = []): Config
    {
        return $this->config = new Config($options);
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

    public function testAdd(): void
    {
        $expected = [
            ...$this->config->toArray(),
            'key' => 'value',
        ];

        $this->config->set('key', 'value');

        Assert::assertSame($expected, $this->config->toArray());
        Assert::assertTrue($this->config->has('key'));
        Assert::assertSame('value', $this->config->get('key'));
    }

    public function testAddAndGetUsingDotNotation(): void
    {
        $this->setUpConfig([
            'app' => 'key',
        ]);
        $this->config->set('foo.bar.baz', 'foo-bar-baz');

        Assert::assertSame([
            'app' => 'key',
            'foo' => [
                'bar' => [
                    'baz' => 'foo-bar-baz',
                ],
            ],
        ], $this->config->toArray());
        Assert::assertSame([
            'bar' => [
                'baz' => 'foo-bar-baz',
            ],
        ], $this->config->get('foo'));
        Assert::assertSame([
            'baz' => 'foo-bar-baz',
        ], $this->config->get('foo.bar'));
        Assert::assertSame('foo-bar-baz', $this->config->get('foo.bar.baz'));
    }

    public function testAddArray(): void
    {
        $this->config->merge([
            'key1' => 'value1',
            'key2' => 'value2',
        ]);
        Assert::assertSame('value1', $this->config->get('key1'));
        Assert::assertSame('value2', $this->config->get('key2'));
    }

    #[DataProvider('setValidOptionProvider')]
    public function testAddValidOption(string $key, mixed $value): void
    {
        $config = new Config();

        $config->set($key, $value);

        /** @var null|mixed $actual */
        $actual = $config->get($key);

        Assert::assertSame($value, $actual);

        if (is_callable($value)) {
            /** @var callable $actual */
            Assert::assertSame($value(), $actual());
        }

        Assert::assertSame([
            $key => $value,
        ], $config->toArray());

        if (! is_iterable($value)) {
            return;
        }

        $expectedCount = $value instanceof Traversable
            ? iterator_count($value)
            : count($value);
        /** @var iterable $actual */
        Assert::assertCount($expectedCount, $actual);
    }

    public function testAppend(): void
    {
        $this->config->append('array', 'xxx');
        Assert::assertSame('xxx', $this->config->get('array.2'));
    }

    public function testAppendingToANonArrayItem(): void
    {
        $config = new Config([
            'foo' => 'bar',
        ]);

        $config->append('foo', 'baz');

        Assert::assertSame([
            'foo' => ['bar', 'baz'],
        ], $config->toArray());
    }

    public function testAppendWithNewKey(): void
    {
        $this->config->append('new-array-key', 'xxx');
        Assert::assertSame(['xxx'], $this->config->get('new-array-key'));
    }

    public function testGet(): void
    {
        Assert::assertSame('bar', $this->config->get('foo'));
    }

    public function testGetWithDefault(): void
    {
        Assert::assertSame('default', $this->config->get('not-exist', 'default'));
    }

    public function testHasIsFalse(): void
    {
        Assert::assertFalse($this->config->has('not-exist'));
        Assert::assertFalse($this->config->has('foo.not-exist'));
    }

    public function testHasIsTrue(): void
    {
        Assert::assertTrue($this->config->has('foo'));
    }

    public function testInstantiable(): void
    {
        Assert::assertInstanceOf(Config::class, $this->config);
        Assert::assertInstanceOf(ConfigInterface::class, $this->config);
    }

    public function testIsEmpty(): void
    {
        Assert::assertEmpty(new Config());
        Assert::assertNull((new Config())->get('nested.non-existent'));
    }

    public function testItCanAppendValuesToAnArrayItem(): void
    {
        $config = new Config([
            'app' => [
                'vars' => ['foo', 'bar'],
            ],
        ]);

        $config->append('app.vars', 'baz');

        Assert::assertSame(['foo', 'bar', 'baz'], $config->get('app.vars'));

        $config->append('app.vars', ['qux', 'quux']);

        Assert::assertSame(['foo', 'bar', 'baz', 'qux', 'quux'], $config->get('app.vars'));
    }

    public function testItCanBeHandledLikeAnArray(): void
    {
        $config = new Config([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);
        $config['baz'] = 'baz';

        Assert::assertSame('bar', $config['bar']);
        unset($config['bar']);
        Assert::assertArrayNotHasKey('bar', $config);
        Assert::assertNull($config['bar']);

        Assert::assertArrayHasKey('foo', $config);
        Assert::assertSame('foo', $config['foo']);
        Assert::assertSame('baz', $config['baz']);
    }

    public function testItCanBeReturnedAsAnArray(): void
    {
        $config = new Config([
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ]);

        Assert::assertSame([
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ], $config->toArray());
    }

    public function testItCanJoinArray(): void
    {
        $this->setUpConfig([
            'foo' => 'foo',
            'baz' => 'baz',
        ]);

        $config = new Config([
            'bar' => 'rab',
            'baz' => 'zab',
        ]);

        $this->config->join($config->toArray());

        Assert::assertSame('foo', $this->config->get('foo'));
        Assert::assertSame('rab', $this->config->get('bar'));
        Assert::assertSame('zab', $this->config->get('baz'));

        Assert::assertSame([
            'foo' => 'foo',
            'baz' => 'zab',
            'bar' => 'rab',
        ], $this->config->toArray());

        $configFactory = new ConfigFactory();
        $config = $configFactory->create([]);

        Assert::assertSame([], $config->toArray());

        $config = $configFactory->createFromPath(dirname(__DIR__) . '/Fixture/config.local.php', 'config');

        Assert::assertSame([
            'config' => [
                'type' => 'local',
                'local' => [
                    'foo' => 'baz',
                ],
            ],
        ], $config->toArray());

        $config->join($configFactory->createFromPath($this->fixture('testing'))->toArray(), 'config');

        Assert::assertSame([
            'config' => [
                'type' => 'testing',
                'local' => [
                    'foo' => 'baz',
                ],
                'testing' => [
                    'foo' => 'bar',
                ],
            ],
        ], $config->toArray());
    }

    public function testItCanMergeAConfigObjectWithoutOverridingExistingValues(): void
    {
        $config = new Config([
            'foo' => 'foo',
            'baz' => 'baz',
        ]);

        $gifnoc = new Config([
            'bar' => 'rab',
            'baz' => 'zab',
        ]);

        $config->merge($gifnoc->toArray());

        Assert::assertSame('foo', $config->get('foo'));
        Assert::assertSame('rab', $config->get('bar'));
        Assert::assertSame('baz', $config->get('baz'));
    }

    public function testItCanPrependValuesToAnArrayItem(): void
    {
        $config = new Config([
            'app' => [
                'vars' => ['foo', 'bar'],
            ],
        ]);

        $config->prepend('app.vars', 'baz');

        Assert::assertSame(['baz', 'foo', 'bar'], $config->get('app.vars'));

        $config->prepend('app.vars', ['qux', 'quux']);

        Assert::assertSame(['qux', 'quux', 'baz', 'foo', 'bar'], $config->get('app.vars'));
    }

    public function testItCanSetAndRetrieveAClosure(): void
    {
        $this->config->set('all-caps', static fn (string $foo): string => mb_strtoupper($foo));

        /** @var ?callable $callable */
        $callable = $this->config->get('all-caps');

        Assert::assertIsCallable($callable);
        Assert::assertInstanceOf(Closure::class, $callable);
        Assert::assertSame('STRING', $callable('string'));
    }

    public function testItCanSplitIntoASubObject(): void
    {
        $config = new Config([
            'foo' => 'foo1',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ]);

        $bar = $config->wrap('bar');

        Assert::assertSame('barbaz', $bar->get('bar.baz'));
        Assert::assertNull($bar->get('foo'));
    }

    public function testItCanUnsetAnOption(): void
    {
        $this->setUpConfig([
            'foo' => [
                'bar' => 'foobar',
                'baz' => 'foobaz',
            ],
        ]);

        $this->config->remove('foo.baz');
        $this->config->remove('foo.qux');

        Assert::assertTrue($this->config->has('foo.bar'));
        Assert::assertFalse($this->config->has('foo.baz'));
    }

    public function testMergeFromPathWithoutOverridingExistingValues(): void
    {
        $configFactory = new ConfigFactory();
        $config = $configFactory->create([]);

        Assert::assertSame([], $config->toArray());

        $config = $configFactory->createFromPath(dirname(__DIR__) . '/Fixture/config.local.php', 'config');

        Assert::assertSame([
            'config' => [
                'type' => 'local',
                'local' => [
                    'foo' => 'baz',
                ],
            ],
        ], $config->toArray());

        Assert::assertSame(1, $config->count());

        $config->merge($configFactory->createFromPath($this->fixture('testing'))->toArray(), 'config');

        Assert::assertSame([
            'config' => [
                'type' => 'local',
                'testing' => [
                    'foo' => 'bar',
                ],
                'local' => [
                    'foo' => 'baz',
                ],
            ],
        ], $config->toArray());

        Assert::assertCount(1, $config);
    }

    public function testOffsetExists(): void
    {
        Assert::assertArrayHasKey('foo', $this->config);
        Assert::assertArrayNotHasKey('not-exist', $this->config);
    }

    public function testOffsetGet(): void
    {
        Assert::assertNull($this->config['not-exist']);
        Assert::assertSame('bar', $this->config['foo']);
        Assert::assertSame([
            'x' => 'xxx',
            'y' => 'yyy',
        ], $this->config['associate']);
    }

    public function testOffsetSet(): void
    {
        Assert::assertArrayNotHasKey('key', $this->config);

        Assert::assertNull($this->config['key']);

        $this->config['key'] = 'value';

        Assert::assertArrayHasKey('key', $this->config);

        Assert::assertNotNull($this->config['key']);

        Assert::assertSame('value', $this->config['key']);
    }

    public function testOffsetUnset(): void
    {
        Assert::assertArrayHasKey('associate', $this->config->toArray());
        Assert::assertSame($this->config['associate'], $this->config->get('associate'));

        unset($this->config['associate']);

        Assert::assertArrayNotHasKey('associate', $this->config->toArray());
        Assert::assertNull($this->config->get('associate'));
    }

    public function testPrepend(): void
    {
        $this->config->prepend('array', 'xxx');
        Assert::assertSame('xxx', $this->config->get('array.0'));
    }

    public function testPrependingToANonArrayItem(): void
    {
        $config = new Config([
            'foo' => 'bar',
        ]);

        $config->prepend('foo', 'baz');
        Assert::assertSame([
            'foo' => ['baz', 'bar'],
        ], $config->toArray());
    }

    public function testPrependWithNewKey(): void
    {
        $this->config->prepend('new_key', 'xxx');
        Assert::assertSame(['xxx'], $this->config->get('new_key'));
    }

    public function testReturnsDefaultConfigOptionValueIfConfigOptionDoesNotExist(): void
    {
        Assert::assertNull($this->config->get('does-not-exist'));
        Assert::assertTrue($this->config->get('does-not-exist', true));
        Assert::assertFalse($this->config->get('does-not-exist', false));
        Assert::assertInstanceOf(stdClass::class, $this->config->get('does-not-exist', new stdClass()));
    }

    public function testReturnsFalseIfKeyDoesNotExist(): void
    {
        Assert::assertFalse($this->config->has('does-not-exist'));
    }

    public function testReturnsNullIfConfigOptionDoesNotExist(): void
    {
        Assert::assertNull($this->config->get('does-not-exist'));
    }

    public function testReturnsTrueIfHas(): void
    {
        $this->config = new Config([
            'has' => 'some-item',
        ]);

        Assert::assertTrue($this->config->has('has'));
    }

    public function testReturnsTrueIfKeyExist(): void
    {
        $config = new Config([
            'foo' => [
                'bar' => 'foobar',
            ],
        ]);

        Assert::assertTrue($config->has('foo.bar'));
    }

    public function testReturnsTrueIfKeyIsBooleanFalse(): void
    {
        $this->config = new Config([
            'false' => false,
        ]);

        Assert::assertTrue($this->config->has('false'));
    }

    public function testSet(): void
    {
        $this->config->set('key', 'value');
        Assert::assertSame('value', $this->config->get('key'));
    }

    public function testToArray(): void
    {
        Assert::assertSame($this->configuration, $this->config->toArray());
    }

    public function testWrap(): void
    {
        $config = new Config([
            'foo' => 'bar',
            'foobar' => ['foo', 'baz'],
        ]);
        Assert::assertSame([
            'foo' => 'bar',
        ], $config->wrap('foo')  ->toArray());
        Assert::assertSame([
            'foobar' => ['foo', 'baz'],
        ], $config->wrap('foobar')  ->toArray());
    }
}

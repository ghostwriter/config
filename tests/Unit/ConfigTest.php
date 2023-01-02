<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit;

use Closure;
use EmptyIterator;
use Generator;
use Ghostwriter\Config\Config;
use Ghostwriter\Config\Contract\ConfigInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFixedArray;
use stdClass;


use Traversable;
use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;

/**
 * @covers \Ghostwriter\Config\Config
 *
 * @internal
 *
 * @small
 */
final class ConfigTest extends TestCase
{
    private array $configuration = [];

    private ConfigInterface $config;

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

        $this->config = new Config($this->configuration);
    }

    /**
     * @template TValue
     *
     * @param array<string, TValue> $options
     */
    final public function setUpConfig(array $options = []): ConfigInterface
    {
        return $this->config = new Config($options);
    }

    /**
     * @covers \Ghostwriter\Config\Config::set
     *
     * @dataProvider setValidOptionProvider
     */
    public function testAddValidOption(string $key, mixed $value): void
    {
        $this->config = new Config();

        $this->config->set($key, $value);

        /** @var null|mixed $actual */
        $actual = $this->config->get($key);

        self::assertSame($value, $actual);

        if (is_callable($value)) {
            /** @var callable $actual */
            self::assertSame($value(), $actual());
        }

        self::assertSame([
            $key => $value,
        ], $this->config->toArray());

        if (is_iterable($value)) {
            /** @var iterable $actual */
            self::assertCount($value instanceof Traversable ? iterator_count($value) : count($value), $actual);
        }
    }

    public function testInstantiable(): void
    {
        self::assertInstanceOf(Config::class, $this->config);
        self::assertInstanceOf(ConfigInterface::class, $this->config);
    }

    public function testMergeFromPathWithoutOverridingExistingValues(): void
    {
        $config = new Config();
        self::assertSame([], $config->toArray());

        $config->mergeFromPath(dirname(__DIR__) . '/Fixture/config.local.php', 'config');

        self::assertSame([
            'config' => [
                'type' => 'local',
                'local' => [
                    'foo' => 'baz',
                ],
            ],
        ], $config->toArray());

        $config->mergeFromPath(dirname(__DIR__) . '/Fixture/config.testing.php', 'config');
        self::assertSame([
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
    }

    public function testIsEmpty(): void
    {
        self::assertEmpty(new Config());
    }

    /** @covers \Ghostwriter\Config\Config::set */
    public function testAdd(): void
    {
        $expected = [
            ...$this->config->toArray(),
            'key' => 'value',
        ];

        $this->config->set('key', 'value');

        self::assertSame($expected, $this->config->toArray());
        // self::assertSame([], $this->config->toArray());
        // self::assertSame([], $this->config);

        self::assertTrue($this->config->has('key'));
        self::assertSame('value', $this->config->get('key'));
    }

    /**
     * @covers \Ghostwriter\Config\Config::set
     * @covers \Ghostwriter\Config\Config::get
     */
    public function testAddAndGetUsingDotNotation(): void
    {
        $this->setUpConfig([
            'app'=>'key',
        ]);
        $this->config->set('foo.bar.baz', 'foo-bar-baz');

        self::assertSame([
            'app' => 'key',
            'foo' => [
                'bar' => [
                    'baz' => 'foo-bar-baz',
                ],
            ],
        ], $this->config->toArray());
        self::assertSame([
            'bar' => [
                'baz' => 'foo-bar-baz',
            ],
        ], $this->config->get('foo'));
        self::assertSame([
            'baz' => 'foo-bar-baz',
        ], $this->config->get('foo.bar'));
        self::assertSame('foo-bar-baz', $this->config->get('foo.bar.baz'));
    }

    /** @covers \Ghostwriter\Config\Config::get */
    public function testReturnsNullIfConfigOptionDoesNotExist(): void
    {
        self::assertNull($this->config->get('does-not-exist'));
    }

    /** @covers \Ghostwriter\Config\Config::get */
    public function testReturnsDefaultConfigOptionValueIfConfigOptionDoesNotExist(): void
    {
        self::assertNull($this->config->get('does-not-exist'));
        self::assertTrue($this->config->get('does-not-exist', true));
        self::assertFalse($this->config->get('does-not-exist', false));
        self::assertInstanceOf(stdClass::class, $this->config->get('does-not-exist', new stdClass()));
    }

    /** @covers \Ghostwriter\Config\Config::has */
    public function testReturnsTrueIfHas(): void
    {
        $this->config = new Config([
            'has' => 'some-item',
        ]);

        self::assertTrue($this->config->has('has'));
    }

    /** @covers \Ghostwriter\Config\Config::has */
    public function testReturnsTrueIfKeyIsBooleanFalse(): void
    {
        $this->config = new Config([
            'false' => false,
        ]);

        self::assertTrue($this->config->has('false'));
    }

    /** @covers \Ghostwriter\Config\Config::has */
    public function testReturnsFalseIfKeyDoesNotExist(): void
    {
        self::assertFalse($this->config->has('does-not-exist'));
    }

    /** @covers \Ghostwriter\Config\Config::has */
    public function testReturnsTrueIfKeyExist(): void
    {
        $config = new Config([
            'foo' => [
                'bar' => 'foobar',
            ],
        ]);

        self::assertTrue($config->has('foo.bar'));
    }

    /**
     * @covers \Ghostwriter\Config\Config::merge
     */
    public function testItCanMergeArray(): void
    {
        $this->setUpConfig([
            'foo' => 'foo',
            'baz' => 'baz',
        ]);

        $config = new Config([
            'bar' => 'rab',
            'baz' => 'zab',
        ]);

        $this->config->merge($config->toArray());

        self::assertSame('foo', $this->config->get('foo'));
        self::assertSame('rab', $this->config->get('bar'));
        self::assertSame('zab', $this->config->get('baz'));

        self::assertSame([
            'foo' => 'foo',
            'baz' => 'zab',
            'bar' => 'rab',
        ], $this->config->toArray());
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

        $config->mergeConfig($gifnoc);

        self::assertSame('foo', $config->get('foo'));
        self::assertSame('rab', $config->get('bar'));
        self::assertSame('baz', $config->get('baz'));
    }

    public function testItCanSplitIntoASubObject(): void
    {
        $config = new Config([
            'foo' => 'foo1',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ]);

        $bar = $config->split('bar');

        self::assertSame('barbaz', $bar->get('baz'));
        self::assertNull($bar->get('foo'));
    }

    public function testItCanSetAndRetrieveAClosure(): void
    {
        $this->config->set('all-caps', static fn (string $foo): string => mb_strtoupper($foo));

        $callable = $this->config->get('all-caps');

        self::assertIsCallable($callable);
        self::assertInstanceOf(Closure::class, $callable);
        self::assertSame('STRING', $callable('string'));
    }

    public function testItCanBeHandledLikeAnArray(): void
    {
        $config        = new Config([
            'foo' => 'foo',
            'bar' => 'bar',
        ]);
        $config['baz'] = 'baz';

        self::assertSame('bar', $config['bar']);
        unset($config['bar']);
        self::assertTrue(isset($config['bar']));
        self::assertNull($config['bar']);

        self::assertTrue(isset($config['foo']));
        self::assertSame('foo', $config['foo']);
        self::assertSame('baz', $config['baz']);
    }

    public function testItCanBeReturnedAsAnArray(): void
    {
        $config = new Config([
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ]);

        self::assertSame([
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ], $config->toArray());
    }

    public function testIsIterable(): void
    {
        $config = new Config([
            'foo' => true,
            'bar' => true,
            'baz' => true,
        ]);

        self::assertIsIterable($config);

        foreach ($config as $item) {
            self::assertTrue($item);
        }
    }

    public function testItCanAppendValuesToAnArrayItem(): void
    {
        $config = new Config([
            'app' => [
                'vars' => ['foo', 'bar'],
            ],
        ]);

        $config->append('app.vars', 'baz');

        self::assertSame(['foo', 'bar', 'baz'], $config->get('app.vars'));

        $config->append('app.vars', ['qux', 'quux']);

        self::assertSame(['foo', 'bar', 'baz', 'qux', 'quux'], $config->get('app.vars'));
    }

    public function testItThrowsAnErrorWhenAppendingToANonArrayItem(): void
    {
        $config = new Config([
            'foo' => 'foo',
        ]);

        $this->expectException(RuntimeException::class);

        $config->append('foo', 'bar');
    }

    public function testItCanPrependValuesToAnArrayItem(): void
    {
        $config = new Config([
            'app' => [
                'vars' => ['foo', 'bar'],
            ],
        ]);

        $config->prepend('app.vars', 'baz');

        self::assertSame(['baz', 'foo', 'bar'], $config->get('app.vars'));

        $config->prepend('app.vars', ['qux', 'quux']);

        self::assertSame(['qux', 'quux', 'baz', 'foo', 'bar'], $config->get('app.vars'));
    }

    public function testItThrowsAnErrorWhenPrependingToANonArrayItem(): void
    {
        $config = new Config([
            'foo' => 'foo',
        ]);

        $this->expectException(RuntimeException::class);

        $config->prepend('foo', 'bar');
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

        self::assertTrue($this->config->has('foo.bar'));
        self::assertFalse($this->config->has('foo.baz'));
    }

     /** @covers \Ghostwriter\Config\Config::push */
     public function testPush(): void
     {
         $this->config->push('array', 'xxx');
         self::assertSame('xxx', $this->config->get('array.2'));
     }

     /** @covers \Ghostwriter\Config\Config::push */
     public function testPushWithNewKey(): void
     {
         $this->config->push('new-array-key', 'xxx');
         self::assertSame(['xxx'], $this->config->get('new-array-key'));
     }

     /** @covers \Ghostwriter\Config\Config::toArray */
     public function testToArray(): void
     {
         self::assertSame($this->configuration, $this->config->toArray());
     }

     public function testOffsetExists(): void
     {
         self::assertTrue(isset($this->config['foo']));
         self::assertFalse(isset($this->config['not-exist']));
     }

     public function testOffsetGet(): void
     {
         self::assertNull($this->config['not-exist']);
         self::assertSame('bar', $this->config['foo']);
         self::assertSame([
             'x' => 'xxx',
             'y' => 'yyy',
         ], $this->config['associate']);
     }

     public function testOffsetSet(): void
     {
         self::assertNull($this->config['key']);

         $this->config['key'] = 'value';

         self::assertSame('value', $this->config['key']);
     }

     public function testOffsetUnset(): void
     {
         self::assertArrayHasKey('associate', $this->config->toArray());
         self::assertSame($this->config['associate'], $this->config->get('associate'));

         unset($this->config['associate']);

         self::assertArrayHasKey('associate', $this->config->toArray());
         self::assertNull($this->config->get('associate'));
     }

     public function testHasIsTrue(): void
     {
         self::assertTrue($this->config->has('foo'));
     }

     public function testHasIsFalse(): void
     {
         self::assertFalse($this->config->has('not-exist'));
     }

     public function testGet(): void
     {
         self::assertSame('bar', $this->config->get('foo'));
     }

//     public function testGetWithArrayOfKeys(): void
//     {
//         $this->assertSame([
//             'foo' => 'bar',
//             'bar' => 'baz',
//             'none' => null,
//         ], $this->config->get([
//             'foo',
//             'bar',
//             'none',
//         ]));
//
//         $this->assertSame([
//             'x.y' => 'default',
//             'x.z' => 'zoo',
//             'bar' => 'baz',
//             'baz' => 'bat',
//         ], $this->config->get([
//             'x.y' => 'default',
//             'x.z' => 'default',
//             'bar' => 'default',
//             'baz',
//         ]));
//     }

//     public function testGetMany(): void
//     {
//         $this->assertSame([
//             'foo' => 'bar',
//             'bar' => 'baz',
//             'none' => null,
//         ], $this->config->getMany([
//             'foo',
//             'bar',
//             'none',
//         ]));
//
//         $this->assertSame([
//             'x.y' => 'default',
//             'x.z' => 'zoo',
//             'bar' => 'baz',
//             'baz' => 'bat',
//         ], $this->config->getMany([
//             'x.y' => 'default',
//             'x.z' => 'default',
//             'bar' => 'default',
//             'baz',
//         ]));
//     }

     public function testGetWithDefault(): void
     {
         self::assertSame('default', $this->config->get('not-exist', 'default'));
     }

     public function testSet(): void
     {
         $this->config->set('key', 'value');
         self::assertSame('value', $this->config->get('key'));
     }

     public function testAddArray(): void
     {
         $this->config->merge([
             'key1' => 'value1',
             'key2' => 'value2',
         ]);
         self::assertSame('value1', $this->config->get('key1'));
         self::assertSame('value2', $this->config->get('key2'));
     }

     public function testPrepend(): void
     {
         $this->config->prepend('array', 'xxx');
         self::assertSame('xxx', $this->config->get('array.0'));
     }

     public function testPrependWithNewKey(): void
     {
         $this->config->prepend('new_key', 'xxx');
         self::assertSame(['xxx'], $this->config->get('new_key'));
     }

    private function setValidOptionProvider(): Generator
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

    private function setInvalidOptionProvider(): Generator
    {
        yield 'string' => ['key', stdClass::class];
        // yield 'float' => ['float', 0.5];
        // yield 'int' => ['int', 1];
        // yield 'object' => ['object', new stdClass];
        // yield 'Closure' => ['Closure', static fn (): bool => true];
        // yield 'array' => ['array', ['key' => 'value']];
        // yield 'nested-array' => ['nested-array', ['nested' => ['array' => ['key' => 'value']]]];
    }
}

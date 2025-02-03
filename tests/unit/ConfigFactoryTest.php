<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\ConfigInterface;
use Ghostwriter\Config\ConfigProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

use function is_file;
use function mb_strtoupper;

#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class ConfigFactoryTest extends AbstractTestCase
{
    public function testAdd(): void
    {
        $config = $this->configFile(self::fixture('dev'));

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
        $config = $this->config([
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

    #[DataProvider('validPaths')]
    public function testConfigFactoryRequirePath(string $path, string $key): void
    {
        $config = $this->configFile($path);

        self::assertTrue($config->has($key));

        self::assertFileExists($path);

        if (is_file($path)) {
            /** @var array|ConfigProviderInterface $options */
            $options = require $path;

            if ($options instanceof ConfigProviderInterface) {
                $newConfig = $this->config([
                    $key => [],
                ]);

                $options($newConfig);

                $options = $newConfig->toArray();

                //                dump($config, $newConfig);
                //                self::assertSame([], $options);
            }

            self::assertIsArray($options);

            self::assertSame($options, $config->get($key));
        }
    }

    public function testConfigFactoryRequirePathFixtureDirectory(): void
    {
        $fixtureDirectory = self::fixtureDirectory();

        $config = $this->configDirectory($fixtureDirectory);

        self::assertTrue($config->has('ci'));
        self::assertTrue($config->has('database'));
        self::assertTrue($config->has('database.mysql'));
        self::assertTrue($config->has('database.pgsql'));
    }

    public function testDefault(): void
    {
        $config = $this->config([
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
        $config = $this->config([
            'foo' => 'bar',
        ]);

        self::assertSame('bar', $config->get('foo'));
    }

    public function testGetExistingWithDefault(): void
    {
        $config = $this->config([
            'exist' => 'exist',
        ]);

        self::assertSame('exist', $config->get('exist', 'default'));
    }

    public function testGetNonExistingWithDefault(): void
    {
        $config = $this->config();

        self::assertSame('default', $config->get('not-exist', 'default'));
    }

    public function testHasIsFalse(): void
    {
        $config = $this->config();

        self::assertFalse($config->has('not-exist'));
        self::assertFalse($config->has('foo.not-exist'));
    }

    public function testHasIsTrue(): void
    {
        $config = $this->config([
            'foo' => 'bar',
        ]);

        self::assertTrue($config->has('foo'));
    }

    public function testInstantiable(): void
    {
        $config = $this->config();

        self::assertInstanceOf(Config::class, $config);
        self::assertInstanceOf(ConfigInterface::class, $config);
    }

    public function testIsEmpty(): void
    {
        $config = $this->config();

        self::assertEmpty($config->toArray());
        self::assertNull($config->get('nested.non-existent'));
    }

    public function testIsEmptyWithDefault(): void
    {
        $config = $this->config();

        self::assertEmpty($config->toArray());
        self::assertSame('default', $config->get('nested.non-existent', 'default'));
    }

    public function testIsEmptyWithDefaultArray(): void
    {
        $config = $this->config();

        self::assertEmpty($config->toArray());

        $default = [
            'nested' => true,
            'non-existent' => true,
            'default' => true,
        ];

        self::assertSame($default, $config->get('nested.non-existent', $default));
    }

    public function testIsEmptyWithDefaultEmptyArray(): void
    {
        $config = $this->config();

        self::assertEmpty($config->toArray());
        self::assertSame([], $config->get('nested.non-existent', []));
    }

    public function testIsEmptyWithDefaultNestedArray(): void
    {
        $config = $this->config();

        self::assertEmpty($config->toArray());

        $default = [
            'nested' => true,
            'default' => true,
        ];

        self::assertSame($default, $config->get('nested', $default));
    }

    public function testItCanBeReturnedAsAnArray(): void
    {
        $expected = [
            'foo' => 'foo',
            'bar' => [
                'baz' => 'barbaz',
            ],
        ];

        $config = $this->config($expected);

        self::assertSame($expected, $config->toArray());
    }

    public function testItCanSetAndRetrieveAClosure(): void
    {
        $config = $this->config();

        $config->set('all-caps', static fn (string $foo): string => mb_strtoupper($foo));

        /** @var ?callable $callable */
        $callable = $config->get('all-caps');

        self::assertIsCallable($callable);
        self::assertInstanceOf(Closure::class, $callable);
        self::assertSame('STRING', $callable('string'));
    }

    public function testItCanSplitIntoASubObject(): void
    {
        $config = $this->config([
            'foo' => 'foo1',
            'bar' => [
                'baz' => 'barBaz',
            ],
        ]);

        self::assertSame('barBaz', $config->get('bar.baz'));
    }

    public function testItCanUnsetAnOption(): void
    {
        $config = $this->config([
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

    public function testReturnsDefaultConfigOptionValueIfConfigOptionDoesNotExist(): void
    {
        $config = $this->config();

        self::assertNull($config->get('does-not-exist'));
        self::assertTrue($config->get('does-not-exist', true));
        self::assertFalse($config->get('does-not-exist', false));
        self::assertInstanceOf(stdClass::class, $config->get('does-not-exist', new stdClass()));
    }

    public function testReturnsFalseIfKeyDoesNotExist(): void
    {
        $config = $this->config();

        self::assertFalse($config->has('does-not-exist'));
    }

    public function testReturnsNullIfConfigOptionDoesNotExist(): void
    {
        $config = $this->config();

        self::assertNull($config->get('does-not-exist'));
    }

    public function testReturnsTrueIfHas(): void
    {
        $config = $this->config([
            'has' => 'some-item',
        ]);

        self::assertTrue($config->has('has'));
    }

    public function testReturnsTrueIfKeyExist(): void
    {
        $config = $this->config([
            'foo' => [
                'bar' => 'foobar',
            ],
        ]);

        self::assertTrue($config->has('foo.bar'));
    }

    public function testReturnsTrueIfKeyIsBooleanFalse(): void
    {
        $config = $this->config([
            'false' => false,
        ]);

        self::assertTrue($config->has('false'));
    }

    public function testSet(): void
    {
        $config = $this->config();
        $config->set('key', 'value');
        self::assertSame('value', $config->get('key'));
    }

    public function testToArray(): void
    {
        $configurations = [
            'foo' => 'bar',
        ];

        $config = $this->config($configurations);

        self::assertSame($configurations, $config->toArray());
    }
}

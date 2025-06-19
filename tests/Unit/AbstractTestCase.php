<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use EmptyIterator;
use ErrorException;
use Generator;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\ConfigFactoryInterface;
use Ghostwriter\Config\ConfigInterface;
use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use PHPUnit\Framework\TestCase;
use SplFixedArray;
use stdClass;

use const DIRECTORY_SEPARATOR;
use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;

use function array_key_exists;
use function dirname;
use function implode;
use function mb_strtolower;
use function realpath;
use function restore_error_handler;
use function set_error_handler;

abstract class AbstractTestCase extends TestCase
{
    protected ConfigFactoryInterface $configFactory;

    final protected function setUp(): void
    {
        set_error_handler(
            /**
             * @throws ErrorException
             */
            static function (int $severity, string $message, string $file, int $line): never {
                throw new ErrorException($message, 255, $severity, $file, $line);
            },
        );

        $this->configFactory = ConfigFactory::new();

        parent::setUp();
    }

    final protected function tearDown(): void
    {
        parent::tearDown();

        restore_error_handler();
    }

    final public function config(array $options = []): ConfigInterface
    {
        return $this->configFactory->create($options);
    }

    final public function configDirectory(string $path): ConfigInterface
    {
        return $this->configFactory->createFromDirectory($path);
    }

    final public function configFile(string $path): ConfigInterface
    {
        return $this->configFactory->createFromFile($path);
    }

    final public static function fixture(string $path): string
    {
        static $paths = [];

        if (array_key_exists($path, $paths)) {
            return $paths[$path];
        }

        $realpath = realpath(
            implode(DIRECTORY_SEPARATOR, [self::fixtureDirectory(), mb_strtolower($path)]) . '.php'
        );

        if (false === $realpath) {
            throw new ConfigFileNotFoundException($path);
        }

        return $paths[$path] = $realpath;
    }

    final public static function fixtureDirectory(): string
    {
        static $realpath;

        if (null !== $realpath) {
            return $realpath;
        }

        $path = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Fixture', 'config']);

        $realpath = realpath($path);

        if (false === $realpath) {
            throw new ConfigFileNotFoundException($path);
        }

        return $realpath;
    }

    /**
     * @return Generator<string,array{string,mixed}>
     */
    public static function setValidOptionProvider(): Generator
    {
        yield from [
            Closure::class => [
                Closure::class,
/** @return true */ static fn (): bool => true,
            ],
            EmptyIterator::class => [EmptyIterator::class, new EmptyIterator()],
            SplFixedArray::class => [SplFixedArray::class, new SplFixedArray()],
            'bool-true' => ['true', true],
            'bool-false' => ['false', false],
            'empty' => ['array', []],
            'float-max' => ['float', PHP_FLOAT_MAX],
            'float-min' => ['float', PHP_FLOAT_MIN],
            'int-max' => ['int', PHP_INT_MAX],
            'int-min' => ['int', PHP_INT_MIN],
            'list' => ['list', ['string', 2, 3.7, true, false, null, new stdClass()]],
            'nested-array' => [
                'nested-array', [
                    'nested' => [
                        'array' => [
                            'key' => 'value',
                        ],
                    ],
                ]],
            'non-empty-array' => [
                'array', [
                    'key' => 'value',
                ]],
            'null' => ['null', null],
            'object' => ['object', new stdClass()],
            'string' => ['foo', 'bar'],
        ];
    }

    /**
     * @return Generator<string,list<string>>
     */
    public static function validPaths(): Generator
    {
        yield from [
            'local' => [self::fixture('local'), 'local'],
            'testing' => [self::fixture('testing'), 'testing'],
            'ci' => [self::fixture('ci'), 'ci'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit;

use Closure;
use EmptyIterator;
use ErrorException;
use Generator;
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
use function sprintf;

abstract class AbstractTestCase extends TestCase
{
    private static string $fixtureDirectory;

    final public static function fixture(string $path): string
    {
        static $paths = [];

        if (array_key_exists($path, $paths)) {
            return $paths[$path];
        }

        $realpath = realpath(
            implode(DIRECTORY_SEPARATOR, [self::fixtureDirectory('valid'), mb_strtolower($path)]) . '.php'
        );

        if (false === $realpath) {
            throw new ErrorException(sprintf('Fixture file "%s" not found.', $path));
        }

        return $paths[$path] = $realpath;
    }

    final public static function fixtureDirectory(string ...$segments): string
    {
        $path = implode(DIRECTORY_SEPARATOR, [self::fixtureDirectoryPath(), ...$segments]);

        $realpath = realpath($path);

        if (false === $realpath) {
            throw new ErrorException(sprintf('Fixture directory "%s" not found.', $path));
        }

        return $realpath;
    }

    /** @return Generator<string,array{string,mixed}> */
    public static function setInvalidOptionProvider(): Generator
    {
        yield from [
            Closure::class => [
                Closure::class,
/** @return true */ static fn (): bool => true,
            ],
            EmptyIterator::class => [EmptyIterator::class, new EmptyIterator()],
            SplFixedArray::class => [SplFixedArray::class, new SplFixedArray()],
            'object' => ['list', new stdClass()],
        ];
    }

    /** @return Generator<string,array{string,mixed}> */
    public static function setValidOptionProvider(): Generator
    {
        yield from [
            'bool-true' => ['true', true],
            'bool-false' => ['false', false],
            'empty' => ['array', []],
            'float-max' => ['float', PHP_FLOAT_MAX],
            'float-min' => ['float', PHP_FLOAT_MIN],
            'int-max' => ['int', PHP_INT_MAX],
            'int-min' => ['int', PHP_INT_MIN],
            'list' => ['list', ['string', 2, 3.7, true, false, null, []]],
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
            'string' => ['foo', 'bar'],
        ];
    }

    /** @return Generator<string,list<string>> */
    public static function validPaths(): Generator
    {
        yield from [
            'local' => [
                self::fixture('local'), 'local', [
                    'foo'=>'baz',
                ]],
            'testing' => [self::fixture('testing'),
                'testing',
                [
                    'foo' => 'bar',
                ]],
            'ci' => [self::fixture('ci'),
                'ci',
                [
                    'foo' => 'bar',
                ]],
        ];
    }

    final protected static function fixtureDirectoryPath(): string
    {
        return self::$fixtureDirectory ??= implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'Fixture']);
    }
}

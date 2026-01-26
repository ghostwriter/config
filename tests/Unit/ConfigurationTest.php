<?php

declare(strict_types=1);

namespace Tests\Unit;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotFoundException;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(Configuration::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
final class ConfigurationTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testAppendMultipleNullValues(): void
    {
        $configuration = Configuration::new();
        $configuration->append('list', null);
        $configuration->append('list', null);
        $configuration->append('list', null);
        self::assertSame([], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testAppendMultipleTimes(): void
    {
        $configuration = Configuration::new();
        $configuration->append('list', 'a');
        $configuration->append('list', 'b');
        $configuration->append('list', 'c');
        self::assertSame(['a', 'b', 'c'], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingArrayWithArrayMerges(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->append('k', ['b', 'c']);
        self::assertSame(['a', 'b', 'c'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingArrayWithNullLeavesUnchanged(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->append('k', null);
        self::assertSame(['a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingArrayWithScalarAppends(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->append('k', 'b');
        self::assertSame(['a', 'b'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingNestedArrayValue(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => ['x'],
                ],
            ],
        ]);
        $configuration->append('a/b/c', 'y');
        self::assertSame(['x', 'y'], $configuration->get('a\b\c'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingScalarWithArrayCreatesMergedArray(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->append('k', ['y', 'z']);
        self::assertSame(['x', 'y', 'z'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingScalarWithNullCreatesArrayWithOldOnly(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->append('k', null);
        self::assertSame(['x'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnExistingScalarWithScalarCreatesArrayOfTwo(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->append('k', 'y');
        self::assertSame(['x', 'y'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnMissingWithArrayCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->append('k', ['x', 'y']);
        self::assertSame(['x', 'y'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnMissingWithNullCreatesEmptyArray(): void
    {
        $configuration = Configuration::new();
        $configuration->append('k', null);
        self::assertSame([], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnMissingWithScalarCreatesArrayWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->append('k', 'v');
        self::assertSame(['v'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendOnNestedArrayWithArrayMerges(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => ['a'],
            ],
        ]);
        $configuration->append('parent.child', ['b', 'c']);
        self::assertSame(['a', 'b', 'c'], $configuration->get('parent.child'));
    }

    /** @throws Throwable */
    public function testAppendOnNestedExistingNullValueCreatesArray(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => null,
            ],
        ]);
        $configuration->append('a.b', 'x');
        self::assertSame(['x'], $configuration->get('a.b'));
    }

    /** @throws Throwable */
    public function testAppendOnNestedKeyWhenParentDoesNotExist(): void
    {
        $configuration = Configuration::new();
        $configuration->append('level1.level2.level3', 'value');
        self::assertSame(['value'], $configuration->get('level1.level2.level3'));
    }

    /** @throws Throwable */
    public function testAppendOnSingleSegmentKeyWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->append('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testAppendOnTwoLevelNestedNonExistingKey(): void
    {
        $configuration = Configuration::new();
        $configuration->append('a\b', 'value');
        self::assertSame(['value'], $configuration->get('a/b'));
    }

    /** @throws Throwable */
    public function testAppendOnVeryDeepMissingParentsCreatesStructure(): void
    {
        $configuration = Configuration::new();

        $configuration->append('u.v.w.x.y', 'z');

        self::assertSame(['z'], $configuration->get('u\v\w/x/y'));
    }

    /** @throws Throwable */
    public function testAppendPromotesNullParentToArray(): void
    {
        $configuration = Configuration::new([
            'parent' => null,
        ]);

        $configuration->append('parent.child', 'value');

        self::assertSame(['value'], $configuration->get('parent.child'));
        self::assertSame([
            'parent' => [
                'child' => ['value'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testAppendToDeepNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => ['x'],
                ],
            ],
        ]);
        $configuration->append('a.b.c', 'y');
        self::assertSame(['x', 'y'], $configuration->get('a.b.c'));
    }

    /** @throws Throwable */
    public function testAppendToExistingNullValueCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        $configuration->append('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testAppendToNullCreatesArrayWithValue(): void
    {
        $configuration = Configuration::new([
            'key' => null,
        ]);
        $configuration->append('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testAppendWithArrayContainingConfigurationInstance(): void
    {
        $configuration = Configuration::new();
        $configuration->append('list', [
            Configuration::new([
                'k' => 'v',
            ])]);
        self::assertSame([
            [
                'k' => 'v',
            ],
        ], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testAppendWithBooleanValue(): void
    {
        $configuration = Configuration::new();
        $configuration->append('flags', true);
        $configuration->append('flags', false);
        self::assertSame([true, false], $configuration->get('flags'));
    }

    /** @throws Throwable */
    public function testAppendWithConfigurationValueOnExistingArrayMerges(): void
    {
        $configuration = Configuration::new([
            'cfg' => [
                'x' => 0,
            ],
        ]);
        $configuration->append('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'x' => 0,
            'a' => 1,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testAppendWithConfigurationValueOnExistingScalarCreatesMergedArray(): void
    {
        $configuration = Configuration::new([
            'cfg' => 'scalar',
        ]);
        $configuration->append('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'scalar',
            'a' => 1,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testAppendWithConfigurationValueOnMissingKeyCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->append('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'a' => 1,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testAppendWithDeepNumericPathCreatesStructure(): void
    {
        $configuration = Configuration::new();
        $configuration->append('matrix.0.1', 'x');
        self::assertSame(['x'], $configuration->get('matrix.0.1'));
        self::assertSame([
            'matrix' => [
                '0' => [
                    '1' => ['x'],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testAppendWithEmptyArrayCreatesEmptyArrayWhenMissing(): void
    {
        $configuration = Configuration::new();
        $configuration->append('empty', []);
        self::assertSame([], $configuration->get('empty'));
    }

    /** @throws Throwable */
    public function testAppendWithEmptyArrayMergesWithExistingArray(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->append('k', []);
        self::assertSame(['a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testAppendWithNumericStringKey(): void
    {
        $configuration = Configuration::new();
        $configuration->append('items.0', 'first');
        self::assertSame([
            'items' => [
                '0' => ['first'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testConstructorWithEmptyArrayCreatesEmptyConfiguration(): void
    {
        self::assertSame([], Configuration::new()->toArray());
    }

    /** @throws Throwable */
    public function testConstructorWithNestedArraysCreatesNestedConfiguration(): void
    {
        self::assertSame('value', Configuration::new([
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ])->get('level1.level2.level3'));
    }

    /** @throws Throwable */
    public function testGetDefaultWhenDeepIntermediateScalar(): void
    {
        self::assertSame('default', Configuration::new([
            'a' => [
                'b' => 'scalar',
            ],
        ])->get('a.b.c.d', 'default'));
    }

    /** @throws Throwable */
    public function testGetDefaultWhenVeryDeepMissingAtEnd(): void
    {
        self::assertSame('default', Configuration::new([
            'a' => [
                'b' => [
                    'c' => [],
                ],
            ],
        ])->get('a.b.c.d.e', 'default'));
    }

    /** @throws Throwable */
    public function testGetOnEmptyConfigReturnsDefault(): void
    {
        self::assertSame('default', Configuration::new()->get('any.key', 'default'));
    }

    /** @throws Throwable */
    public function testGetOnNestedKeyWhenIntermediateIsNull(): void
    {
        self::assertSame('default', Configuration::new([
            'parent' => null,
        ])->get('parent.child', 'default'));
    }

    /** @throws Throwable */
    public function testGetOnSingleSegmentKeyReturnsValue(): void
    {
        self::assertSame('value', Configuration::new([
            'key' => 'value',
        ])->get('key'));
    }

    /** @throws Throwable */
    public function testGetOnTwoLevelNestedKey(): void
    {
        self::assertSame('value', Configuration::new([
            'a' => [
                'b' => 'value',
            ],
        ])->get('a.b'));
    }

    /** @throws Throwable */
    public function testGetReturnsDeepNestedValue(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'v',
                ],
            ],
        ]);
        self::assertSame('v', $configuration->get('a.b.c'));
    }

    /** @throws Throwable */
    public function testGetReturnsDefaultForDeeplyNestedMissingKey(): void
    {
        $configuration = Configuration::new([
            'existing' => 'value',
        ]);
        self::assertSame('default', $configuration->get('nonexistent.deeply.nested.key', 'default'));
    }

    /** @throws Throwable */
    public function testGetReturnsDefaultForMissingKey(): void
    {
        $configuration = Configuration::new();
        self::assertSame('def', $configuration->get('missing', 'def'));
    }

    /** @throws Throwable */
    public function testGetReturnsDefaultWhenIntermediateSegmentNotArray(): void
    {
        $configuration = Configuration::new([
            'parent' => 'scalar',
        ]);
        self::assertSame('d', $configuration->get('parent.child', 'd'));
    }

    /** @throws Throwable */
    public function testGetReturnsEmptyStringValue(): void
    {
        $configuration = Configuration::new([
            'empty' => '',
        ]);
        self::assertSame('', $configuration->get('empty'));
    }

    /** @throws Throwable */
    public function testGetReturnsFalseValue(): void
    {
        $configuration = Configuration::new([
            'bool' => false,
        ]);
        self::assertFalse($configuration->get('bool'));
    }

    /** @throws Throwable */
    public function testGetReturnsNestedArrayWhenPartialKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'c',
            ],
        ]);
        self::assertSame([
            'b' => 'c',
        ], $configuration->get('a'));
    }

    /** @throws Throwable */
    public function testGetReturnsNullForExplicitNullValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        self::assertNull($configuration->get('key'));
        self::assertNull($configuration->get('key', 'should-not-use-default'));
    }

    /** @throws Throwable */
    public function testGetReturnsValueForExistingKey(): void
    {
        $configuration = Configuration::new([
            'foo' => 'bar',
        ]);
        self::assertSame('bar', $configuration->get('foo'));
    }

    /** @throws Throwable */
    public function testGetReturnsZeroValue(): void
    {
        $configuration = Configuration::new([
            'zero' => 0,
        ]);
        self::assertSame(0, $configuration->get('zero'));
    }

    /** @throws Throwable */
    public function testGetWithDefaultOnNestedMissingKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'value',
            ],
        ]);
        self::assertSame('default', $configuration->get('a.c', 'default'));
    }

    /** @throws Throwable */
    public function testGetWithMixedSeparatorsResolvesPath(): void
    {
        $configuration = Configuration::new();
        $configuration->set('u\\v.w/x', 'z');
        self::assertSame('z', $configuration->get('u.v.w.x'));
        self::assertSame('z', $configuration->get('u/v/w/x'));
        self::assertSame('z', $configuration->get('u\\v\\w\\x'));
    }

    /** @throws Throwable */
    public function testGetWithMultipleLevelsOfNesting(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => [
                            'e' => 'deep',
                        ],
                    ],
                ],
            ],
        ]);
        self::assertSame('deep', $configuration->get('a.b.c.d.e'));
    }

    /** @throws Throwable */
    public function testGetWithNestedNullValue(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => null,
            ],
        ]);
        self::assertNull($configuration->get('a.b'));
        self::assertTrue($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testHasOnEmptyConfigReturnsFalse(): void
    {
        $configuration = Configuration::new();
        self::assertFalse($configuration->has('any.key'));
    }

    /** @throws Throwable */
    public function testHasOnNestedKeyWhenIntermediateIsNull(): void
    {
        $configuration = Configuration::new([
            'parent' => null,
        ]);
        self::assertFalse($configuration->has('parent.child'));
    }

    /** @throws Throwable */
    public function testHasOnSingleSegmentKeyReturnsTrue(): void
    {
        $configuration = Configuration::new([
            'key' => 'value',
        ]);
        self::assertTrue($configuration->has('key'));
    }

    /** @throws Throwable */
    public function testHasOnTwoLevelNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'value',
            ],
        ]);
        self::assertTrue($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseForDeeplyNestedMissingKey(): void
    {
        $configuration = Configuration::new([
            'existing' => 'value',
        ]);
        self::assertFalse($configuration->has('nonexistent.very.deeply.nested.key.path'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseForMissingKey(): void
    {
        $configuration = Configuration::new();
        self::assertFalse($configuration->has('nope'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseWhenDeepIntermediateScalar(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'scalar',
                ],
            ],
        ]);

        self::assertFalse($configuration->has('a.b.c.d'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseWhenDeepLeafMissing(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [],
                ],
            ],
        ]);

        self::assertFalse($configuration->has('a.b.c.d'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseWhenIntermediateSegmentMissing(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 1,
            ],
        ]);
        self::assertFalse($configuration->has('a.c.d'));
    }

    /** @throws Throwable */
    public function testHasReturnsFalseWhenIntermediateSegmentNotArray(): void
    {
        $configuration = Configuration::new([
            'a' => 'scalar',
        ]);
        self::assertFalse($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testHasReturnsTrueForEmptyStringValue(): void
    {
        $configuration = Configuration::new([
            'empty' => '',
        ]);
        self::assertTrue($configuration->has('empty'));
    }

    /** @throws Throwable */
    public function testHasReturnsTrueForExistingKey(): void
    {
        $configuration = Configuration::new([
            'x' => 1,
        ]);
        self::assertTrue($configuration->has('x'));
    }

    /** @throws Throwable */
    public function testHasReturnsTrueForFalseValue(): void
    {
        $configuration = Configuration::new([
            'f' => false,
        ]);
        self::assertTrue($configuration->has('f'));
    }

    /** @throws Throwable */
    public function testHasReturnsTrueForNullValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('null', null);
        self::assertTrue($configuration->has('null'));
    }

    /** @throws Throwable */
    public function testHasReturnsTrueForZeroValue(): void
    {
        $configuration = Configuration::new([
            'zero' => 0,
        ]);
        self::assertTrue($configuration->has('zero'));
    }

    /** @throws Throwable */
    public function testHasWithBackslashSeparatedPath(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'x',
                ],
            ],
        ]);

        self::assertTrue($configuration->has('a\\b\\c'));
    }

    /** @throws Throwable */
    public function testHasWithMultipleLevelsOfNesting(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => [
                            'e' => 'deep',
                        ],
                    ],
                ],
            ],
        ]);
        self::assertTrue($configuration->has('a.b.c.d.e'));
        self::assertFalse($configuration->has('a.b.c.d.f'));
    }

    /** @throws Throwable */
    public function testMergeAcceptsPermittedArrayValue(): void
    {
        $configuration = Configuration::new([
            'arr' => ['a', 'b'],
        ]);

        // Triggers normalizeScalarOrNullOrThrow -> isPermittedConfigurationValue(array)
        $configuration->merge([
            'arr' => ['c', 'd'],
        ]);

        self::assertSame(['a', 'b', 'c', 'd'], $configuration->get('arr'));
    }

    /** @throws Throwable */
    public function testMergeAcceptsPermittedEmptyArrayValue(): void
    {
        $configuration = Configuration::new([
            'arr' => [],
        ]);

        // Triggers normalizeScalarOrNullOrThrow -> isPermittedConfigurationValue(array)
        $configuration->merge([
            'arr' => [],
        ]);

        self::assertSame([], $configuration->get('arr'));
    }

    /** @throws Throwable */
    public function testMergeAcceptsPermittedNullValue(): void
    {
        $configuration = Configuration::new();

        // Triggers normalizeScalarOrNullOrThrow -> isPermittedConfigurationValue(null)
        $configuration->merge([
            'nullable' => null,
        ]);

        self::assertTrue($configuration->has('nullable'));
        self::assertNull($configuration->get('nullable'));
    }

    /** @throws Throwable */
    public function testMergeAcceptsPermittedScalarValue(): void
    {
        $configuration = Configuration::new();

        // Triggers normalizeScalarOrNullOrThrow -> isPermittedConfigurationValue(string)
        $configuration->merge([
            'scalar' => 'value',
        ]);

        self::assertSame('value', $configuration->get('scalar'));
    }

    /** @throws Throwable */
    public function testMergeCreatesHierarchyForDeepMissingSegments(): void
    {
        $configuration = Configuration::new();

        $configuration->merge([
            'alpha.beta.gamma' => 'value',
        ]);

        self::assertSame([
            'alpha' => [
                'beta' => [
                    'gamma' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeDirectoryIgnoresNonPhpFilesInNestedSubdirectories(): void
    {
        $configuration = Configuration::new();

        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_nested_' . uniqid();
        $sub  = $root . DIRECTORY_SEPARATOR . 'sub';

        mkdir($root);
        mkdir($sub);

        $rootPhp = $root . DIRECTORY_SEPARATOR . 'root.php';
        $subPhp  = $sub . DIRECTORY_SEPARATOR . 'inner.php';
        $subTxt  = $sub . DIRECTORY_SEPARATOR . 'ignore.txt';

        file_put_contents($rootPhp, "<?php\nreturn ['root' => 'R'];\n");
        file_put_contents($subPhp, "<?php\nreturn ['k' => 'v'];\n");
        file_put_contents($subTxt, "not a php config file\n");

        try {
            $configuration->mergeDirectory($root);

            // root.php becomes key `root`
            self::assertTrue($configuration->has('root'));
            self::assertSame('R', $configuration->get('root.root'));

            // sub/inner.php becomes key `sub.inner`
            self::assertTrue($configuration->has('sub.inner'));
            self::assertSame('v', $configuration->get('sub.inner.k'));

            // non-php file is ignored
            self::assertFalse($configuration->has('sub.ignore'));
        } finally {
            @unlink($rootPhp);
            @unlink($subPhp);
            @unlink($subTxt);
            @rmdir($sub);
            @rmdir($root);
        }
    }

    /** @throws Throwable */
    public function testMergeDirectoryLoadsNestedDatabaseConfigs(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeDirectory(self::fixtureDirectory('valid'));
        self::assertTrue($configuration->has('database.mysql'));
        self::assertTrue($configuration->has('database.pgsql'));
    }

    /** @throws Throwable */
    public function testMergeDirectoryLoadsPhpFilesCaseInsensitive(): void
    {
        $configuration = Configuration::new();

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_case_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'UPPER.PHP';
        file_put_contents($file, "<?php\nreturn ['KV' => 'V'];\n");

        try {
            $configuration->mergeDirectory($dir);

            self::assertTrue($configuration->has('UPPER'));
            self::assertSame('V', $configuration->get('UPPER.KV'));
        } finally {
            @unlink($file);
            @rmdir($dir);
        }
    }

    /** @throws Throwable */
    public function testMergeDirectoryMergesAllPhpFilesInDirectory(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeDirectory(self::fixtureDirectory('valid'));
        self::assertTrue($configuration->has('ci'));
        self::assertTrue($configuration->has('database'));
    }

    /** @throws Throwable */
    public function testMergeDirectoryWithEmptyDirectoryDoesNothing(): void
    {
        $configuration = Configuration::new([
            'keep' => 'value',
        ]);

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_empty_' . uniqid();
        mkdir($dir);

        try {
            $configuration->mergeDirectory($dir);
            self::assertSame([
                'keep' => 'value',
            ], $configuration->toArray());
        } finally {
            rmdir($dir);
        }
    }

    /** @throws Throwable */
    public function testMergeDirectoryWithInvalidDirectoryThrowsException(): void
    {
        $configuration = Configuration::new();

        $nonExistentDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_nonexistent_' . uniqid();

        $this->expectException(ConfigurationDirectoryNotFoundException::class);
        $this->expectExceptionMessage('cannot be resolved to a real path');

        $configuration->mergeDirectory($nonExistentDir);
    }

    /** @throws Throwable */
    public function testMergeDirectoryWithValidRealDirectorySucceeds(): void
    {
        $configuration = Configuration::new();

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_valid_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'config.php';
        file_put_contents($file, "<?php\nreturn ['key' => 'value'];\n");

        try {
            $configuration->mergeDirectory($dir);
            self::assertTrue($configuration->has('config.key'));
            self::assertSame('value', $configuration->get('config.key'));
        } finally {
            @unlink($file);
            @rmdir($dir);
        }
    }

    /** @throws Throwable */
    public function testMergeExtendsExistingNestedArray(): void
    {
        $configuration = Configuration::new([
            'alpha' => [
                'beta' => [
                    'existing' => 'present',
                ],
            ],
        ]);

        $configuration->merge([
            'alpha.beta.gamma' => 'value',
        ]);

        self::assertSame([
            'alpha' => [
                'beta' => [
                    'existing' => 'present',
                    'gamma' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeFileLoadsConfigurationCorrectly(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeFile(self::fixture('ci'));
        self::assertTrue($configuration->has('ci'));
        self::assertSame('bar', $configuration->get('ci.foo'));
    }

    /** @throws Throwable */
    public function testMergeFileMultipleTimesAccumulatesValues(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeFile(self::fixture('local'), 'first');
        $configuration->mergeFile(self::fixture('local'), 'second');
        self::assertTrue($configuration->has('first'));
        self::assertTrue($configuration->has('second'));
    }

    /** @throws Throwable */
    public function testMergeFileWithEmptyNamespaceAppendsToRoot(): void
    {
        $configuration = Configuration::new([
            'existing' => 'value',
        ]);
        $configuration->mergeFile(self::fixture('local'));
        self::assertTrue($configuration->has('local'));
        self::assertTrue($configuration->has('existing'));
    }

    /** @throws Throwable */
    public function testMergeFileWithNamespaceMergesUnderKey(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeFile(self::fixture('local'), 'dump');
        self::assertTrue($configuration->has('dump'));
        self::assertTrue($configuration->has('dump.local'));
    }

    /** @throws Throwable */
    public function testMergeFileWithoutNamespaceMergesTopLevel(): void
    {
        $configuration = Configuration::new();
        $configuration->mergeFile(self::fixture('local'));
        self::assertTrue($configuration->has('local'));
        self::assertSame('baz', $configuration->get('local.foo'));
    }

    /** @throws Throwable */
    public function testMergeMergesArraysWithNumericKeys(): void
    {
        $configuration = Configuration::new([
            'list' => ['a'],
        ]);
        $configuration->merge([
            'list' => ['b'],
        ]);
        self::assertSame([
            'list' => ['a', 'b'],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeMergesNestedArraysAndOverwritesScalars(): void
    {
        $configuration = Configuration::new([
            'settings' => [
                'enable' => true,
            ],
            'foo' => 'bar',
        ]);

        $configuration->merge([
            'settings' => [
                'disabled' => false,
            ],
            'foo' => [
                'nested' => 'x',
            ],
        ]);

        self::assertSame([
            'settings' => [
                'enable' => true,
                'disabled' => false,
            ],
            'foo' => [
                'nested' => 'x',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeMultipleKeysWithMixedValueTypesInSingleCall(): void
    {
        $configuration = Configuration::new();

        $configuration->merge([
            'plain' => 'value',
            'nested.array' => [
                'x' => 1,
            ],
            'nullable' => null,
            'cfg' => Configuration::new([
                'z' => 9,
            ]),
        ]);

        self::assertSame('value', $configuration->get('plain'));
        self::assertSame([
            'x' => 1,
        ], $configuration->get('nested.array'));
        self::assertNull($configuration->get('nullable'));
        self::assertSame([
            'z' => 9,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testMergeNormalizesNestedConfigurationInstancesRecursively(): void
    {
        $configuration = Configuration::new();
        $configuration->merge([
            'outer' => Configuration::new([
                'inner' => Configuration::new([
                    'x' => 1,
                ]),
            ]),
        ]);

        self::assertSame([
            'outer' => [
                'inner' => [
                    'x' => 1,
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeOverwritesExistingScalarWithArray(): void
    {
        $configuration = Configuration::new([
            'key' => 'scalar',
        ]);
        $configuration->merge([
            'key' => [
                'nested' => 'value',
            ],
        ]);
        self::assertSame([
            'key' => [
                'nested' => 'value',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergePreservesExistingKeysNotInMergeArray(): void
    {
        $configuration = Configuration::new([
            'keep' => 'this',
            'overwrite' => 'old',
        ]);
        $configuration->merge([
            'overwrite' => 'new',
            'add' => 'value',
        ]);
        self::assertSame([
            'keep' => 'this',
            'overwrite' => 'new',
            'add' => 'value',
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergePromotesMissingIntermediateAfterScalarRoot(): void
    {
        $configuration = Configuration::new([
            'alpha' => 'scalar',
        ]);

        $configuration->merge([
            'alpha.beta' => 'value',
        ]);

        self::assertSame([
            'alpha' => [
                'beta' => 'value',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergePromotesScalarIntermediateToArray(): void
    {
        $configuration = Configuration::new([
            'alpha' => [
                'beta' => 'scalar',
            ],
        ]);

        $configuration->merge([
            'alpha.beta.gamma' => 'value',
        ]);

        self::assertSame([
            'alpha' => [
                'beta' => [
                    'gamma' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeWithArrayContainingConfigurationInstance(): void
    {
        $configuration = Configuration::new([
            'existing' => 'value',
        ]);
        $nested = Configuration::new([
            'nested' => 'config',
        ]);
        $configuration->merge([
            'new' => $nested,
        ]);
        self::assertSame([
            'existing' => 'value',
            'new' => [
                'nested' => 'config',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeWithEmptyArrayDoesNotChangeConfiguration(): void
    {
        $configuration = Configuration::new([
            'a' => 'b',
        ]);
        $configuration->merge([]);
        self::assertSame([
            'a' => 'b',
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeWithNestedConfigurationInstances(): void
    {
        $configuration = Configuration::new([
            'a' => 'b',
        ]);
        $nestedConfig = Configuration::new([
            'c' => 'd',
        ]);
        $configuration->merge([
            'nested' => $nestedConfig,
        ]);
        self::assertSame([
            'a' => 'b',
            'nested' => [
                'c' => 'd',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testMergeWithNullValues(): void
    {
        $configuration = Configuration::new([
            'a' => 'value',
        ]);
        $configuration->merge([
            'a' => null,
            'b' => null,
        ]);
        self::assertNull($configuration->get('a'));
        self::assertNull($configuration->get('b'));
    }

    /** @throws Throwable */
    public function testPrependMultipleNullValues(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('list', null);
        $configuration->prepend('list', null);
        self::assertSame([], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testPrependMultipleTimes(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('list', 'a');
        $configuration->prepend('list', 'b');
        $configuration->prepend('list', 'c');
        self::assertSame(['c', 'b', 'a'], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingArrayWithArrayMergesToFront(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->prepend('k', ['x', 'y']);
        self::assertSame(['x', 'y', 'a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingArrayWithNullPrependsNull(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->prepend('k', null);
        self::assertSame(['a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingArrayWithScalarPrependsValue(): void
    {
        $configuration = Configuration::new([
            'k' => ['a', 'b'],
        ]);
        $configuration->prepend('k', 'z');
        self::assertSame(['z', 'a', 'b'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingNestedArrayValue(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => ['x'],
                ],
            ],
        ]);
        $configuration->prepend('a.b.c', 'y');
        self::assertSame(['y', 'x'], $configuration->get('a.b.c'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingScalarWithArrayMergesToFront(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->prepend('k', ['y', 'z']);
        self::assertSame(['y', 'z', 'x'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingScalarWithNullCreatesArrayWithOldOnly(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->prepend('k', null);
        self::assertSame(['x'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnExistingScalarWithScalarCreatesArrayNewThenOld(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->prepend('k', 'y');
        self::assertSame(['y', 'x'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnMissingWithArrayCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('k', ['a', 'b']);
        self::assertSame(['a', 'b'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnMissingWithNullCreatesArrayWithNull(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('k', null);
        self::assertSame([], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnMissingWithScalarCreatesArrayWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('k', 'v');
        self::assertSame(['v'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testPrependOnNestedArrayWithArrayMerges(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => ['a'],
            ],
        ]);
        $configuration->prepend('parent.child', ['x', 'y']);
        self::assertSame(['x', 'y', 'a'], $configuration->get('parent.child'));
    }

    /** @throws Throwable */
    public function testPrependOnNestedExistingNullValueCreatesArray(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => null,
            ],
        ]);
        $configuration->prepend('a.b', 'x');
        self::assertSame(['x'], $configuration->get('a.b'));
    }

    /** @throws Throwable */
    public function testPrependOnNestedKeyWhenParentDoesNotExist(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('level1.level2.level3', 'value');
        self::assertSame(['value'], $configuration->get('level1.level2.level3'));
    }

    /** @throws Throwable */
    public function testPrependOnSingleSegmentKeyWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testPrependOnTwoLevelNestedNonExistingKey(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('a.b', 'value');
        self::assertSame(['value'], $configuration->get('a.b'));
    }

    /** @throws Throwable */
    public function testPrependOnVeryDeepMissingParentsCreatesStructure(): void
    {
        $configuration = Configuration::new();

        $configuration->prepend('u.v.w.x.y', 'z');

        self::assertSame(['z'], $configuration->get('u.v.w.x.y'));
    }

    /** @throws Throwable */
    public function testPrependPromotesNullParentToArray(): void
    {
        $configuration = Configuration::new([
            'parent' => null,
        ]);

        $configuration->prepend('parent.child', 'value');

        self::assertSame(['value'], $configuration->get('parent.child'));
        self::assertSame([
            'parent' => [
                'child' => ['value'],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testPrependToDeepNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => ['x'],
                ],
            ],
        ]);
        $configuration->prepend('a.b.c', 'y');
        self::assertSame(['y', 'x'], $configuration->get('a.b.c'));
    }

    /** @throws Throwable */
    public function testPrependToExistingNullValueCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        $configuration->prepend('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testPrependToNullCreatesArrayWithValue(): void
    {
        $configuration = Configuration::new([
            'key' => null,
        ]);
        $configuration->prepend('key', 'value');
        self::assertSame(['value'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testPrependWithArrayContainingConfigurationInstance(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('list', [
            Configuration::new([
                'k' => 'v',
            ])]);
        self::assertSame([
            [
                'k' => 'v',
            ],
        ], $configuration->get('list'));
    }

    /** @throws Throwable */
    public function testPrependWithBooleanValue(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('flags', true);
        $configuration->prepend('flags', false);
        self::assertSame([false, true], $configuration->get('flags'));
    }

    /** @throws Throwable */
    public function testPrependWithConfigurationValueOnExistingArrayMergesToFront(): void
    {
        $configuration = Configuration::new([
            'cfg' => [
                'x' => 0,
            ],
        ]);
        $configuration->prepend('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'a' => 1,
            'x' => 0,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testPrependWithConfigurationValueOnExistingScalarCreatesArrayNewThenOld(): void
    {
        $configuration = Configuration::new([
            'cfg' => 'scalar',
        ]);
        $configuration->prepend('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'a' => 1,
            'scalar',
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testPrependWithConfigurationValueOnMissingKeyCreatesArray(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('cfg', Configuration::new([
            'a' => 1,
        ]));
        self::assertSame([
            'a' => 1,
        ], $configuration->get('cfg'));
    }

    /** @throws Throwable */
    public function testPrependWithDeepNumericPathCreatesStructure(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('grid.0.2', 'y');
        self::assertSame(['y'], $configuration->get('grid.0.2'));
        self::assertSame([
            'grid' => [
                '0' => [
                    '2' => ['y'],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testPrependWithEmptyArrayCreatesEmptyArrayWhenMissing(): void
    {
        $configuration = Configuration::new();
        $configuration->prepend('empty', []);
        self::assertSame([], $configuration->get('empty'));
    }

    /** @throws Throwable */
    public function testPrependWithEmptyArrayMergesWithExistingArray(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->prepend('k', []);
        self::assertSame(['a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testResetAfterMultipleOperations(): void
    {
        $configuration = Configuration::new([
            'a' => 'b',
        ]);
        $configuration->set('c', 'd');
        $configuration->append('e', 'f');
        $configuration->prepend('g', 'h');
        self::assertTrue($configuration->has('a'));
        self::assertTrue($configuration->has('c'));
        self::assertTrue($configuration->has('e'));
        self::assertTrue($configuration->has('g'));
        $configuration->reset();
        self::assertFalse($configuration->has('a'));
        self::assertFalse($configuration->has('c'));
        self::assertFalse($configuration->has('e'));
        self::assertFalse($configuration->has('g'));
        self::assertSame([], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testResetClearsAllConfiguration(): void
    {
        $configuration = Configuration::new([
            'a' => 'b',
        ]);
        self::assertTrue($configuration->has('a'));
        $configuration->reset();
        self::assertFalse($configuration->has('a'));
        self::assertSame([], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testResetOnNewConfigurationDoesNothing(): void
    {
        $configuration = Configuration::new();
        $configuration->reset();
        self::assertSame([], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetCreatesArrayOnMissing(): void
    {
        $configuration = Configuration::new();
        $configuration->set('k', ['a']);
        self::assertSame(['a'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetCreatesNullOnMissingAndHasIsTrue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('k', null);
        self::assertTrue($configuration->has('k'));
        self::assertNull($configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetCreatesScalarOnMissing(): void
    {
        $configuration = Configuration::new();
        $configuration->set('k', 'v');
        self::assertSame('v', $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetDeepNestedCreatesIntermediates(): void
    {
        $configuration = Configuration::new();
        $configuration->set('a.b.c', 'v');
        self::assertSame([
            'a' => [
                'b' => [
                    'c' => 'v',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetMergesArrayIntoExistingArray(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->set('k', ['b']);
        self::assertSame(['b'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetMergesEmptyArrayWithExistingArray(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->set('k', []);
        self::assertSame([], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetMergesMultipleArrayLevels(): void
    {
        $configuration = Configuration::new([
            'level1' => [
                'level2' => [
                    'a' => '1',
                ],
            ],
        ]);
        $configuration->set('level1.level2', [
            'b' => '2',
        ]);
        self::assertSame([
            'level1' => [
                'level2' => [
                    'b' => '2',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetMergesNumericKeyArrays(): void
    {
        $configuration = Configuration::new([
            'list' => [
                0 => 'a',
                1 => 'b',
            ],
        ]);
        $configuration->set('list', [
            2 => 'c',
            3 => 'd',
        ]);
        $result = $configuration->get('list');
        self::assertIsArray($result);
        self::assertNotContains('a', $result);
        self::assertNotContains('b', $result);
        self::assertContains('c', $result);
        self::assertContains('d', $result);
    }

    /** @throws Throwable */
    public function testSetOnExistingArrayKeyWithScalarReplacesArray(): void
    {
        $configuration = Configuration::new([
            'key' => [
                'existing' => 'array',
            ],
        ]);
        $configuration->set('key', 'scalar');
        self::assertSame('scalar', $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnExistingNestedArrayMergesArrays(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => [
                    'a' => '1',
                ],
            ],
        ]);
        $configuration->set('parent.child', [
            'b' => '2',
        ]);
        self::assertSame([
            'parent' => [
                'child' => [
                    'b' => '2',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetOnExistingNullKeyWithArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        $configuration->set('key', ['array']);
        self::assertSame(['array'], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnExistingNullKeyWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        $configuration->set('key', 'scalar');
        self::assertSame('scalar', $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnExistingScalarKeyWithArrayReplacesScalar(): void
    {
        $configuration = Configuration::new([
            'key' => 'scalar',
        ]);
        $configuration->set('key', [
            'new' => 'array',
        ]);
        self::assertSame([
            'new' => 'array',
        ], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnMultiLevelNestedNonExistingKey(): void
    {
        $configuration = Configuration::new();
        $configuration->set('a.b.c.d.e', 'deep');
        self::assertSame('deep', $configuration->get('a.b.c.d.e'));
    }

    /** @throws Throwable */
    public function testSetOnNonExistingNestedKeyWithArrayCreatesStructure(): void
    {
        $configuration = Configuration::new();
        $configuration->set('level1.level2.level3', [
            'key' => 'value',
        ]);
        self::assertSame([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'key' => 'value',
                    ],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetOnSingleSegmentKeyWithArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', [
            'nested' => 'value',
        ]);
        self::assertSame([
            'nested' => 'value',
        ], $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnSingleSegmentKeyWithNull(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', null);
        self::assertNull($configuration->get('key'));
        self::assertTrue($configuration->has('key'));
    }

    /** @throws Throwable */
    public function testSetOnSingleSegmentKeyWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->set('key', 'scalar');
        self::assertSame('scalar', $configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOnTwoLevelNestedKeyWithArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('a.b', [
            'c' => 'd',
        ]);
        self::assertSame([
            'c' => 'd',
        ], $configuration->get('a.b'));
    }

    /** @throws Throwable */
    public function testSetOverwritesArrayWithNullValue(): void
    {
        $configuration = Configuration::new([
            'key' => [
                'array' => 'value',
            ],
        ]);
        $configuration->set('key', null);
        self::assertNull($configuration->get('key'));
    }

    /** @throws Throwable */
    public function testSetOverwritesDeepNestedValue(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'old',
                ],
            ],
        ]);
        $configuration->set('a.b.c', 'new');
        self::assertSame('new', $configuration->get('a.b.c'));
    }

    /** @throws Throwable */
    public function testSetOverwritesScalarWithNull(): void
    {
        $configuration = Configuration::new([
            'key' => 'scalar',
        ]);
        $configuration->set('key', null);
        self::assertNull($configuration->get('key'));
        self::assertTrue($configuration->has('key'));
    }

    /** @throws Throwable */
    public function testSetPromotesNullParentToArray(): void
    {
        $configuration = Configuration::new([
            'parent' => null,
        ]);

        $configuration->set('parent.child', 'value');

        self::assertSame('value', $configuration->get('parent.child'));
        self::assertSame([
            'parent' => [
                'child' => 'value',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetReplacesArrayWithNull(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->set('k', null);
        self::assertTrue($configuration->has('k'));
        self::assertNull($configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetReplacesArrayWithScalar(): void
    {
        $configuration = Configuration::new([
            'k' => ['a'],
        ]);
        $configuration->set('k', 'v');
        self::assertSame('v', $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetReplacesNullWithArray(): void
    {
        $configuration = Configuration::new();
        $configuration->set('k', null);
        self::assertNull($configuration->get('k'));
        $configuration->set('k', ['a', 'b']);
        self::assertSame(['a', 'b'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetReplacesNullWithScalar(): void
    {
        $configuration = Configuration::new();
        $configuration->set('k', null);
        self::assertNull($configuration->get('k'));
        $configuration->set('k', 'value');
        self::assertSame('value', $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetReplacesScalarWithArray(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->set('k', ['y']);
        self::assertSame(['y'], $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetReplacesScalarWithScalar(): void
    {
        $configuration = Configuration::new([
            'k' => 'x',
        ]);
        $configuration->set('k', 'y');
        self::assertSame('y', $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testSetWithBackslashSeparatedKeySetsValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('a\\b\\c', 'v');
        self::assertSame('v', $configuration->get('a.b.c'));
        self::assertSame('v', $configuration->get('a/b/c'));
    }

    /** @throws Throwable */
    public function testSetWithBooleanTrueValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('flag', true);
        self::assertTrue($configuration->get('flag'));
    }

    /** @throws Throwable */
    public function testSetWithEmptyArrayValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('empty', []);
        self::assertSame([], $configuration->get('empty'));
        self::assertTrue($configuration->has('empty'));
    }

    /** @throws Throwable */
    public function testSetWithEmptyStringValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('empty', '');
        self::assertSame('', $configuration->get('empty'));
        self::assertTrue($configuration->has('empty'));
    }

    /** @throws Throwable */
    public function testSetWithFalseValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('bool', false);
        self::assertFalse($configuration->get('bool'));
        self::assertTrue($configuration->has('bool'));
    }

    /** @throws Throwable */
    public function testSetWithFloatValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('float', 3.14);
        self::assertSame(3.14, $configuration->get('float'));
    }

    /** @throws Throwable */
    public function testSetWithNegativeIntValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('negative', -42);
        self::assertSame(-42, $configuration->get('negative'));
    }

    /** @throws Throwable */
    public function testSetWithNestedConfigurationInstanceInArray(): void
    {
        $configuration = Configuration::new();
        $nested = Configuration::new([
            'inner' => 'value',
        ]);
        $configuration->set('outer', [
            'config' => $nested,
        ]);
        self::assertSame([
            'outer' => [
                'config' => [
                    'inner' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testSetWithZeroValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('zero', 0);
        self::assertSame(0, $configuration->get('zero'));
        self::assertTrue($configuration->has('zero'));
    }

    /** @throws Throwable */
    public function testToArrayConvertsNestedConfigurationInstances(): void
    {
        $configuration = Configuration::new([
            'foo' => Configuration::new([
                'bar' => 'baz',
            ]),
        ]);
        self::assertSame([
            'foo' => [
                'bar' => 'baz',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayNormalizesNestedConfigurationInstancesInsideList(): void
    {
        $configuration = Configuration::new();
        $configuration->set('list', [
            Configuration::new([
                'k' => 'v',
            ]),
            [
                'nested' => Configuration::new([
                    'x' => 1,
                ]),
            ],
            2,
            's',
        ]);

        self::assertSame([
            'list' => [
                [
                    'k' => 'v',
                ],
                [
                    'nested' => [
                        'x' => 1,
                    ],
                ],
                2,
                's',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayPreservesNullValues(): void
    {
        $configuration = Configuration::new();
        $configuration->set('a', null);
        $configuration->set('b', 'value');
        $configuration->set('c', null);
        self::assertSame([
            'a' => null,
            'b' => 'value',
            'c' => null,
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayWithDeeplyNestedConfigurationInstances(): void
    {
        $configuration = Configuration::new([
            'level1' => Configuration::new([
                'level2' => Configuration::new([
                    'level3' => 'value',
                ]),
            ]),
        ]);
        self::assertSame([
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayWithEmptyConfiguration(): void
    {
        $configuration = Configuration::new();
        self::assertSame([], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayWithMixedConfigurationInstances(): void
    {
        $configuration = Configuration::new([
            'plain' => 'value',
            'nested' => Configuration::new([
                'deep' => Configuration::new([
                    'key' => 'val',
                ]),
            ]),
        ]);
        self::assertSame([
            'plain' => 'value',
            'nested' => [
                'deep' => [
                    'key' => 'val',
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testToArrayWithScalarValues(): void
    {
        $configuration = Configuration::new([
            'string' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
        ]);
        self::assertSame([
            'string' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetDeepNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'value',
                    ],
                ],
            ],
        ]);
        $configuration->unset('a.b.c.d');
        self::assertFalse($configuration->has('a.b.c.d'));
        self::assertTrue($configuration->has('a.b.c'));
        self::assertSame([
            'a' => [
                'b' => [
                    'c' => [],
                ],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetDoesNotAffectOtherNestedKeys(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child1' => 'value1',
                'child2' => 'value2',
                'child3' => 'value3',
            ],
        ]);
        $configuration->unset('parent.child2');
        self::assertTrue($configuration->has('parent.child1'));
        self::assertFalse($configuration->has('parent.child2'));
        self::assertTrue($configuration->has('parent.child3'));
        self::assertSame([
            'parent' => [
                'child1' => 'value1',
                'child3' => 'value3',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetDoesNothingForMissingTopLevelKey(): void
    {
        $configuration = Configuration::new([
            'k' => 'v',
        ]);
        $configuration->unset('missing');
        self::assertTrue($configuration->has('k'));
        self::assertSame('v', $configuration->get('k'));
    }

    /** @throws Throwable */
    public function testUnsetDoesNothingWhenIntermediateMissing(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 1,
            ],
        ]);
        $configuration->unset('a.c.d');
        self::assertTrue($configuration->has('a.b'));
        self::assertFalse($configuration->has('a.c'));
    }

    /** @throws Throwable */
    public function testUnsetDoesNothingWhenParentIsScalar(): void
    {
        $configuration = Configuration::new([
            'a' => 'scalar',
        ]);
        $configuration->unset('a.b');
        self::assertTrue($configuration->has('a'));
        self::assertSame('scalar', $configuration->get('a'));
    }

    /** @throws Throwable */
    public function testUnsetLeafArrayTriggersBaseCaseThenRemovesKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [
                        'x' => 'y',
                    ],
                ],
            ],
        ]);

        // Unset a leaf that is an array; this will exercise the empty-segments base case in the recursion
        $configuration->unset('a.b.c');

        self::assertFalse($configuration->has('a.b.c'));
        self::assertTrue($configuration->has('a.b'));
        self::assertSame([
            'a' => [
                'b' => [],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetOnDeepNumericPathRemovesTarget(): void
    {
        $configuration = Configuration::new();
        // Build a numeric-keyed nested structure via append
        $configuration->append('grid.0.2', 'y');

        // Sanity
        self::assertTrue($configuration->has('grid.0.2'));

        // Remove the deep numeric path
        $configuration->unset('grid.0.2');

        self::assertFalse($configuration->has('grid.0.2'));
        self::assertTrue($configuration->has('grid.0'));
        self::assertSame([
            'grid' => [
                '0' => [],
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetOnNestedKeyWhenParentIsArray(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child1' => 'value1',
                'child2' => 'value2',
            ],
        ]);
        $configuration->unset('parent.child1');
        self::assertFalse($configuration->has('parent.child1'));
        self::assertTrue($configuration->has('parent.child2'));
    }

    /** @throws Throwable */
    public function testUnsetOnNestedKeyWithSingleSegmentPath(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'value',
            ],
        ]);
        $configuration->unset('a');
        self::assertFalse($configuration->has('a'));
        self::assertFalse($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testUnsetOnSingleSegmentKeyRemovesKey(): void
    {
        $configuration = Configuration::new([
            'key' => 'value',
        ]);
        $configuration->unset('key');
        self::assertFalse($configuration->has('key'));
    }

    /** @throws Throwable */
    public function testUnsetOnTwoLevelNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'value',
            ],
        ]);
        $configuration->unset('a.b');
        self::assertFalse($configuration->has('a.b'));
        self::assertTrue($configuration->has('a'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesEmptyStringValue(): void
    {
        $configuration = Configuration::new([
            'empty' => '',
        ]);
        self::assertTrue($configuration->has('empty'));
        $configuration->unset('empty');
        self::assertFalse($configuration->has('empty'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesFalseValue(): void
    {
        $configuration = Configuration::new([
            'bool' => false,
        ]);
        self::assertTrue($configuration->has('bool'));
        $configuration->unset('bool');
        self::assertFalse($configuration->has('bool'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesLiteralDotKeyViaExactMatch(): void
    {
        $configuration = Configuration::new([
            'a.b' => 'value',
        ]);
        $configuration->unset('a.b');
        self::assertFalse($configuration->has('a.b'));
        self::assertSame([
            'a' => [],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetRemovesNestedKeyAndLeavesParentArray(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => 'c',
                'd' => 'e',
            ],
        ]);
        $configuration->unset('a.b');
        self::assertFalse($configuration->has('a.b'));
        self::assertTrue($configuration->has('a.d'));
        self::assertSame([
            'a' => [
                'd' => 'e',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetRemovesNullValue(): void
    {
        $configuration = Configuration::new();
        $configuration->set('null', null);
        self::assertTrue($configuration->has('null'));
        $configuration->unset('null');
        self::assertFalse($configuration->has('null'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesOnlyLastSegmentInNestedStructure(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'value',
                    'd' => 'other',
                ],
            ],
        ]);
        $configuration->unset('a.b.c');
        self::assertFalse($configuration->has('a.b.c'));
        self::assertTrue($configuration->has('a.b.d'));
        self::assertTrue($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesTopLevelKey(): void
    {
        $configuration = Configuration::new([
            'k' => 'v',
        ]);
        $configuration->unset('k');
        self::assertFalse($configuration->has('k'));
    }

    /** @throws Throwable */
    public function testUnsetRemovesZeroValue(): void
    {
        $configuration = Configuration::new([
            'zero' => 0,
        ]);
        self::assertTrue($configuration->has('zero'));
        $configuration->unset('zero');
        self::assertFalse($configuration->has('zero'));
    }

    /** @throws Throwable */
    public function testUnsetReturnsEarlyWhenIntermediateBecomesNull(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => null,
            ],
        ]);

        $configuration->unset('parent.child.grand');

        self::assertTrue($configuration->has('parent.child'));
        self::assertNull($configuration->get('parent.child'));
        self::assertSame([
            'parent' => [
                'child' => null,
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetReturnsEarlyWhenIntermediateBecomesScalar(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => 'scalar',
            ],
        ]);

        $configuration->unset('parent.child.grand');

        self::assertSame('scalar', $configuration->get('parent.child'));
        self::assertSame([
            'parent' => [
                'child' => 'scalar',
            ],
        ], $configuration->toArray());
    }

    /** @throws Throwable */
    public function testUnsetWithBackslashSeparatedPathRemovesOnlyTarget(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'x',
                    'd' => 'y',
                ],
            ],
        ]);

        $configuration->unset('a\\b\\c');

        self::assertFalse($configuration->has('a.b.c'));
        self::assertTrue($configuration->has('a.b.d'));
        self::assertTrue($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testUnsetWithSlashSeparatedPathRemovesOnlyTarget(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => 'x',
                    'd' => 'y',
                ],
            ],
        ]);

        $configuration->unset('a/b/c');
        self::assertFalse($configuration->has('a.b.c'));
        self::assertTrue($configuration->has('a.b.d'));
        self::assertTrue($configuration->has('a.b'));
    }

    /** @throws Throwable */
    public function testWrapDeepNestedKey(): void
    {
        $configuration = Configuration::new([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => 'value',
                    ],
                ],
            ],
        ]);
        $wrapped = $configuration->wrap('a.b.c');
        self::assertSame([
            'd' => 'value',
        ], $wrapped->toArray());
    }

    /** @throws Throwable */
    public function testWrapExistingEmptyArrayReturnsEmptyConfiguration(): void
    {
        $configuration = Configuration::new([
            'empty' => [],
        ]);

        $wrapped = $configuration->wrap('empty');

        self::assertSame([], $wrapped->toArray());
    }

    /** @throws Throwable */
    public function testWrapReturnsConfigWithDefaultWhenMissing(): void
    {
        $configuration = Configuration::new();
        $wrapped = $configuration->wrap('non.existent', [
            'fallback' => 'fallback-value',
        ]);
        self::assertInstanceOf(ConfigurationInterface::class, $wrapped);
        self::assertTrue($wrapped->has('fallback'));
        self::assertSame('fallback-value', $wrapped->get('fallback'));
    }

    /** @throws Throwable */
    public function testWrapReturnsConfigWithLastSegmentForExistingKey(): void
    {
        $configuration = Configuration::new([
            'parent' => [
                'child' => 'val',
            ],
        ]);
        $wrapped = $configuration->wrap('parent');
        self::assertInstanceOf(ConfigurationInterface::class, $wrapped);
        self::assertSame([
            'child' => 'val',
        ], $wrapped->toArray());
        self::assertSame('val', $wrapped->get('child'));
    }

    /** @throws Throwable */
    public function testWrapSingleSegmentKeepsKeyName(): void
    {
        $configuration = Configuration::new([
            'single' => [
                'key' => 'value',
            ],
        ]);
        $wrapped = $configuration->wrap('single');
        self::assertSame([
            'key' => 'value',
        ], $wrapped->toArray());
    }

    /** @throws Throwable */
    public function testWrapUsesLastSegmentWithMultipleDots(): void
    {
        $configuration = Configuration::new();
        $configuration->set('top.middle.bottom', [
            'key' => 'value',
        ]);
        $wrapped = $configuration->wrap('top.middle.bottom');
        self::assertSame([
            'key' => 'value',
        ], $wrapped->toArray());
    }

    /** @throws Throwable */
    public function testWrapWithEmptyDefault(): void
    {
        $configuration = Configuration::new();
        $wrapped = $configuration->wrap('missing');
        self::assertInstanceOf(ConfigurationInterface::class, $wrapped);
        self::assertSame([], $wrapped->toArray());
    }

    /** @throws Throwable */
    public function testWrapWithNestedDefaultStructure(): void
    {
        $expected = [
            'default' => [
                'nested' => 'value',
            ],
        ];
        self::assertSame($expected, Configuration::new()->wrap('missing', $expected)->toArray());
    }

    /** @throws Throwable */
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationFileNotFoundException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function implode;

#[CoversClass(ConfigurationFileNotFoundException::class)]
#[CoversClass(Configuration::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationFileNotFoundExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testCreateFrom(): void
    {
        $path = 'invalid/file/path';

        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationFileNotFoundException::class);

        $this->expectExceptionMessage($path);

        Configuration::new()->mergeFile($path);
    }

    /** @throws Throwable */
    public function testCreateFromDirectory(): void
    {
        $path = implode(DIRECTORY_SEPARATOR, ['invalid', 'directory', 'path', '']);

        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationFileNotFoundException::class);

        $this->expectExceptionMessage($path);

        Configuration::new()->mergeFile($path);
    }

    /** @throws Throwable */
    public function testCreateFromWithDirectoryPath(): void
    {
        $path = implode(DIRECTORY_SEPARATOR, ['invalid', 'directory', 'path', '']);

        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationFileNotFoundException::class);

        $this->expectExceptionMessage($path);

        Configuration::new()->mergeFile($path);
    }

    /** @throws Throwable */
    public function testMergeFileThrowsConfigFileNotFoundException(): void
    {
        $this->expectException(ConfigurationFileNotFoundException::class);

        Configuration::new()->mergeFile('/path/does/not/exist/ghostwriter-config-test/nonexistent.php');
    }
}

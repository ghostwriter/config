<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

use const DIRECTORY_SEPARATOR;

use function implode;

#[CoversClass(ConfigFileNotFoundException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class ConfigFileNotFoundExceptionTest extends AbstractTestCase
{
    public function testCreateFromFile(): void
    {
        $path = 'invalid/file/path';

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ConfigFileNotFoundException::class);

        $this->expectExceptionMessage($path);

        $this->configFile($path);
    }

    public function testCreateFromFileWithDirectoryPath(): void
    {
        $path = implode(DIRECTORY_SEPARATOR, ['invalid', 'directory', 'path', '']);

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ConfigFileNotFoundException::class);

        $this->expectExceptionMessage($path);

        $this->configFile($path);
    }
}

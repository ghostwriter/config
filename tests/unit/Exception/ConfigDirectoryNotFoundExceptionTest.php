<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\ConfigDirectoryNotFoundException;
use Ghostwriter\Config\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

use const DIRECTORY_SEPARATOR;

use function implode;
use function sys_get_temp_dir;
use function tempnam;

#[CoversClass(ConfigDirectoryNotFoundException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class ConfigDirectoryNotFoundExceptionTest extends AbstractTestCase
{
    public function testCreateFromDirectory(): void
    {
        $path = implode(DIRECTORY_SEPARATOR, ['invalid', 'directory', 'path', '']);

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ConfigDirectoryNotFoundException::class);

        $this->expectExceptionMessage($path);

        $this->configDirectory($path);
    }

    public function testCreateFromDirectoryWithFilePath(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'file-is-not-a-directory');

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ConfigDirectoryNotFoundException::class);

        $this->expectExceptionMessage($path);

        $this->configDirectory($path);
    }
}

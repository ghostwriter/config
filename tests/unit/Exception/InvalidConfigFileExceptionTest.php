<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

use function sys_get_temp_dir;
use function tempnam;

#[CoversClass(InvalidConfigFileException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class InvalidConfigFileExceptionTest extends AbstractTestCase
{
    public function testCreateFromFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'invalid-key');

        $this->expectException(ExceptionInterface::class);

        $this->expectException(InvalidConfigFileException::class);

        $this->expectExceptionMessage($path);

        $this->configFile($path);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\ShouldNotHappenException;
use Ghostwriter\Config\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

#[CoversClass(ShouldNotHappenException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class ShouldNotHappenExceptionTest extends AbstractTestCase
{
    public function testCreateFromDirectoryWithEmptyString(): void
    {
        $path = '';

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ShouldNotHappenException::class);

        $this->expectExceptionMessage('Invalid config directory, empty string provided.');

        $this->configDirectory($path);
    }

    public function testCreateFromFileWithEmptyString(): void
    {
        $path = '';

        $this->expectException(ExceptionInterface::class);

        $this->expectException(ShouldNotHappenException::class);

        $this->expectExceptionMessage('Invalid config file, empty string provided.');

        $this->configFile($path);
    }
}

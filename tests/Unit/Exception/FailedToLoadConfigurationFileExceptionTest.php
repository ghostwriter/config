<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\FailedToLoadConfigurationFileException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(Configuration::class)]
#[CoversClass(FailedToLoadConfigurationFileException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class FailedToLoadConfigurationFileExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testFromInvalidFileRaisesException(): void
    {
        $this->expectException(FailedToLoadConfigurationFileException::class);
        $this->expectExceptionMessageMatches('#Failed to load config file: .*invalid'.\DIRECTORY_SEPARATOR.'throws.php#iu');

        Configuration::new()->mergeFile(self::fixtureDirectory('invalid', 'throws.php'));
    }
}

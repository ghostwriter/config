<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\FailedToLoadConfigurationFileWithErrorsException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

#[CoversClass(Configuration::class)]
#[CoversClass(FailedToLoadConfigurationFileWithErrorsException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class FailedToLoadConfigurationFileWithErrorsExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testFromInvalidFileRaisesError(): void
    {
        $this->expectException(FailedToLoadConfigurationFileWithErrorsException::class);

        Configuration::new()->mergeFile(self::fixtureDirectory('invalid', 'errors.php'));
    }
}

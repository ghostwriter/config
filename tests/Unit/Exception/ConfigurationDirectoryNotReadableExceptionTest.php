<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotReadableException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;

use const DIRECTORY_SEPARATOR;

use function chmod;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;

#[CoversClass(Configuration::class)]
#[CoversClass(ConfigurationDirectoryNotReadableException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationDirectoryNotReadableExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testMergeDirectoryThrowsOnNotReadableDirectory(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_config_dir_' . uniqid();
        mkdir($dir);

        // make directory unreadable
        $chmod = chmod($dir, 0);
        if (false === $chmod) {
            self::markTestSkipped('Could not change directory permissions to unreadable.');
        }

        try {
            $this->expectException(ConfigurationDirectoryNotReadableException::class);

            Configuration::new()->mergeDirectory($dir);
        } finally {
            // restore permissions and cleanup
            chmod($dir, 0o755);
            rmdir($dir);
        }
    }
}

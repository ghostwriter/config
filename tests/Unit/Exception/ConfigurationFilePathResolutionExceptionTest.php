<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationFilePathResolutionException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function is_dir;
use function is_file;
use function is_link;
use function mkdir;
use function rmdir;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(ConfigurationFilePathResolutionException::class)]
#[CoversClass(Configuration::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationFilePathResolutionExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testMergeDirectoryThrowsShouldNotHappenExceptionWhenRealpathFails(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_config_broken_' . uniqid();

        mkdir($dir);

        self::assertDirectoryExists($dir);

        $symlink = $dir . DIRECTORY_SEPARATOR . 'broken.php';
        // create a symlink pointing to a non-existent target so getRealPath() returns false
        $created = @symlink('/path/does/not/exist/ghostwriter-broken-target.php', $symlink);

        if (! $created || ! is_link($symlink)) {
            // cleanup and skip on filesystems where symlink is not supported or not permitted
            if (is_file($symlink)) {
                @unlink($symlink);
            }
            @rmdir($dir);

            self::markTestSkipped(
                'Symlink creation failed on this system; cannot reliably test broken symlink behavior.'
            );
        }

        try {
            $this->expectException(ConfigurationFilePathResolutionException::class);

            $this->expectExceptionMessage('Failed to get real path for');

            Configuration::new()->mergeDirectory($dir);
        } finally {
            // cleanup
            if (is_link($symlink)) {
                unlink($symlink);
            }
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }
}

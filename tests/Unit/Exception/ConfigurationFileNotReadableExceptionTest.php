<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationFileNotReadableException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function chmod;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

#[CoversClass(ConfigurationFileNotReadableException::class)]
#[CoversClass(Configuration::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]

final class ConfigurationFileNotReadableExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testCreateFromFile(): void
    {
        $unreadableFile = tempnam(sys_get_temp_dir(), 'unreadable-config');

        // Make the file unreadable
        chmod($unreadableFile, 0o333);

        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(ConfigurationFileNotReadableException::class);

        $this->expectExceptionMessageMatches('#^Config file ".+" is not readable.$#iu');

        Configuration::new()->mergeFile($unreadableFile);
    }

    /** @throws Throwable */
    public function testMergeFileThrowsConfigFileNotReadableException(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_config_test_' . uniqid();
        mkdir($dir);
        $file = $dir . DIRECTORY_SEPARATOR . 'config.php';
        file_put_contents($file, "<?php\nreturn ['foo' => 'bar'];\n");

        // make file unreadable
        chmod($file, 0);

        try {
            $this->expectException(ConfigurationFileNotReadableException::class);

            Configuration::new()->mergeFile($file);
        } finally {
            // restore permissions so we can cleanup
            chmod($file, 0o644);
            unlink($file);
            rmdir($dir);
        }
    }
}

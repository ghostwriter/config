<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotFoundException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function assert;
use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function sys_get_temp_dir;
use function tempnam;
use function time;
use function unlink;

#[CoversClass(Configuration::class)]
#[CoversClass(ConfigurationDirectoryNotFoundException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationDirectoryNotFoundExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testCreateFromDirectoryWithFilePath(): void
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . __FUNCTION__ . DIRECTORY_SEPARATOR . 'temp' . time();
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0o777, true);
        }

        $path = tempnam(dirname($tempDir), 'file-is-not-a-directory.php');

        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationDirectoryNotFoundException::class);

        $this->expectExceptionMessage($path);

        Configuration::new()->mergeDirectory($path);
    }

    /** @throws Throwable */
    public function testMergeDirectoryThrowsWhenNotFound(): void
    {
        $this->expectException(ConfigurationDirectoryNotFoundException::class);

        // use a path very unlikely to exist to trigger not-found behavior
        Configuration::new()->mergeDirectory('/path/does/not/exist/ghostwriter-config-test');
    }

    /** @throws Throwable */
    public function testMergeDirectoryThrowsWhenPathIsAFile(): void
    {
        $this->expectException(ConfigurationDirectoryNotFoundException::class);

        $tmpFile = tempnam(sys_get_temp_dir(), 'gw_cfg_file_');
        assert(false !== $tmpFile);

        try {
            file_put_contents($tmpFile, "<?php\nreturn ['k' => 'v'];\n");
            Configuration::new()->mergeDirectory($tmpFile);
        } finally {
            @unlink($tmpFile);
        }
    }
}

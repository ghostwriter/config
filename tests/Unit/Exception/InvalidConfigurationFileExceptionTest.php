<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\InvalidConfigurationFileException;
use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatImplementInterface;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

#[CoversClass(Configuration::class)]
#[CoversClass(InvalidConfigurationFileException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class InvalidConfigurationFileExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testCreateFromFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'invalid-config');

        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(InvalidConfigurationFileException::class);

        Configuration::new()->mergeFile($path);
    }

    /** @throws Throwable */
    public function testMergeDirectoryThrowsInvalidConfigurationFileException(): void
    {
        $this->expectException(InvalidConfigurationFileException::class);

        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_invalid_' . uniqid();
        mkdir($dir);
        $file = $dir . DIRECTORY_SEPARATOR . 'bad.php';
        file_put_contents($file, "<?php\nreturn 123;\n");

        try {
            Configuration::new()->mergeDirectory($dir);
        } finally {
            @unlink($file);
            @rmdir($dir);
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Exception\ConfigurationKeyMustBeNonEmptyStringException;
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
use function uniqid;
use function unlink;

#[CoversClass(Configuration::class)]
#[CoversClass(ConfigurationKeyMustBeNonEmptyStringException::class)]
#[CoversClassesThatImplementInterface(ConfigurationInterface::class)]
#[CoversClassesThatImplementInterface(ConfigurationExceptionInterface::class)]
final class ConfigurationKeyMustBeNonEmptyStringExceptionTest extends AbstractTestCase
{
    /** @throws Throwable */
    public function testAppendThrowsEmptyConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new()->append('', true);
    }

    /** @throws Throwable */
    public function testAppendThrowsOnEmptyKey(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new()->append('', true);
    }

    /** @throws Throwable */
    public function testCreateEmptyKey(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new([
            '' => 'value',
        ]);
    }

    /** @throws Throwable */
    public function testMergeFileWithEmptyNamespaceThrowsInvalidKeyException(): void
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_empty_ns_' . uniqid();
        mkdir($dir);

        $file = $dir . DIRECTORY_SEPARATOR . 'config.php';
        file_put_contents($file, "<?php\nreturn ['k' => 'v'];\n");

        try {
            $configuration = Configuration::new();
            $this->expectException(ConfigurationExceptionInterface::class);
            $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);
            $configuration->mergeFile($file, '');
        } finally {
            @unlink($file);
            @rmdir($dir);
        }
    }

    /** @throws Throwable */
    public function testMergeSpaceThrowsEmptyConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        $merge = [
            ' ' => true,
        ];

        Configuration::new()->merge($merge);
    }

    /** @throws Throwable */
    public function testMergeThrowsEmptyConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        $merge = [
            '' => true,
        ];

        Configuration::new()->merge($merge);
    }

    /** @throws Throwable */
    public function testPrependThrowsEmptyConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new()->prepend('', true);
    }

    /** @throws Throwable */
    public function testSetEmptyKey(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);

        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new()->set('', 'value');
    }

    /** @throws Throwable */
    public function testSetThrowsInvalidConfigurationKeyException(): void
    {
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);

        Configuration::new()->set('', [true]);
    }

    /** @throws Throwable */
    public function testSetWithWhitespaceKeyThrowsException(): void
    {
        $this->expectException(ConfigurationExceptionInterface::class);
        $this->expectException(ConfigurationKeyMustBeNonEmptyStringException::class);
        Configuration::new()->set('   ', 'value');
    }
}

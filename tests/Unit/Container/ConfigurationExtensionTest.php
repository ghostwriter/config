<?php

declare(strict_types=1);

namespace Tests\Unit\Container;

use Ghostwriter\Config\AbstractConfiguration;
use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Container\ConfigurationExtension;
use Ghostwriter\Config\Exception\ConfigurationDirectoryNotReadableException;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversClassesThatExtendClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Unit\AbstractTestCase;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function chdir;
use function chmod;
use function file_put_contents;
use function getcwd;
use function is_a;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

#[CoversClass(ConfigurationExtension::class)]
#[CoversClassesThatExtendClass(AbstractConfiguration::class)]
#[UsesClass(Configuration::class)]
final class ConfigurationExtensionTest extends AbstractTestCase
{
    public function testExtensionInterface(): void
    {
        $configuration = $this->createMockConfiguration();

        $configuration
            ->expects(self::exactly(0))
            ->method('mergeDirectory')
            ->seal();

        $container = $this->createMockContainer();

        (new ConfigurationExtension())($container, $configuration);
    }

    public function testInstanceOfExtensionInterface(): void
    {
        self::assertTrue(is_a(ConfigurationExtension::class, ExtensionInterface::class, true));
    }

    /** @throws Throwable */
    public function testInvokeLeavesConfigurationUntouchedWhenGhostwriterDirectoryDoesNotExist(): void
    {
        $currentWorkingDirectory = getcwd();

        self::assertNotFalse($currentWorkingDirectory);

        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_ext_missing_' . uniqid();
        mkdir($root);

        $configuration = Configuration::new([
            'keep' => 'value',
        ]);

        try {
            chdir($root);

            (new ConfigurationExtension())($this->createMockContainer(), $configuration);

            self::assertSame([
                'keep' => 'value',
            ], $configuration->toArray());
        } finally {
            chdir($currentWorkingDirectory);
            @rmdir($root);
        }
    }

    /** @throws Throwable */
    public function testInvokeMergesGhostwriterConfigurationFromCurrentWorkingDirectory(): void
    {
        $currentWorkingDirectory = getcwd();

        self::assertNotFalse($currentWorkingDirectory);

        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_ext_merge_' . uniqid();
        $config = $root . DIRECTORY_SEPARATOR . 'config';
        $ghostwriter = $config . DIRECTORY_SEPARATOR . 'ghostwriter';
        $nested = $ghostwriter . DIRECTORY_SEPARATOR . 'database';

        mkdir($nested, 0o777, true);

        $appFile = $ghostwriter . DIRECTORY_SEPARATOR . 'app.php';
        $databaseFile = $nested . DIRECTORY_SEPARATOR . 'pgsql.php';

        file_put_contents($appFile, "<?php\nreturn ['name' => 'Ghostwriter', 'env' => 'test'];\n");
        file_put_contents($databaseFile, "<?php\nreturn ['driver' => 'pgsql', 'port' => 5432];\n");

        $configuration = Configuration::new();

        try {
            chdir($root);

            (new ConfigurationExtension())($this->createMockContainer(), $configuration);

            self::assertSame('Ghostwriter', $configuration->get('ghostwriter.app.name'));
            self::assertSame('test', $configuration->get('ghostwriter.app.env'));
            self::assertSame('pgsql', $configuration->get('ghostwriter.database.pgsql.driver'));
            self::assertSame(5432, $configuration->get('ghostwriter.database.pgsql.port'));
        } finally {
            chdir($currentWorkingDirectory);
            @unlink($databaseFile);
            @unlink($appFile);
            @rmdir($nested);
            @rmdir($ghostwriter);
            @rmdir($config);
            @rmdir($root);
        }
    }

    /** @throws Throwable */
    public function testInvokePropagatesUnreadableDirectoryException(): void
    {
        $currentWorkingDirectory = getcwd();

        self::assertNotFalse($currentWorkingDirectory);

        $root = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gw_cfg_ext_unreadable_' . uniqid();
        $ghostwriter = $root . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'ghostwriter';

        mkdir($ghostwriter, 0o777, true);

        $chmod = chmod($ghostwriter, 0);
        if (false === $chmod) {
            self::markTestSkipped('Could not change directory permissions to unreadable.');
        }

        try {
            chdir($root);

            $this->expectException(ConfigurationDirectoryNotReadableException::class);

            (new ConfigurationExtension())($this->createMockContainer(), Configuration::new());
        } finally {
            chdir($currentWorkingDirectory);
            chmod($ghostwriter, 0o755);
            @rmdir($ghostwriter);
            @rmdir($root . DIRECTORY_SEPARATOR . 'config');
            @rmdir($root);
        }
    }

    /** @return ConfigurationInterface&MockObject */
    private function createMockConfiguration(): ConfigurationInterface
    {
        return $this->createMock(ConfigurationInterface::class);
    }

    private function createMockContainer(): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->expects(self::never())
            ->method('get')
            ->seal();

        return $container;
    }
}

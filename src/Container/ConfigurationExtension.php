<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Override;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function assert;
use function getcwd;
use function implode;
use function is_dir;

/**
 * @see ConfigurationExtensionTest
 *
 * @implements ExtensionInterface<Configuration>
 */
final readonly class ConfigurationExtension implements ExtensionInterface
{
    /**
     * @param ConfigurationInterface $service
     *
     * @throws Throwable
     */
    #[Override]
    public function __invoke(ContainerInterface $container, object $service): void
    {
        assert($service instanceof ConfigurationInterface);

        $currentWorkingDirectory = getcwd();
        if (false === $currentWorkingDirectory) {
            return;
        }

        $configDirectory = implode(DIRECTORY_SEPARATOR, [$currentWorkingDirectory, 'config', 'ghostwriter']);
        if (! is_dir($configDirectory)) {
            return;
        }

        $service->mergeDirectory($configDirectory);
    }
}

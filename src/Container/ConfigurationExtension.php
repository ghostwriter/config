<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Override;
use Throwable;

/**
 * @see ConfigurationExtensionTest
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
        if ($currentWorkingDirectory === false) {
            return;
        }

        $configDirectory = implode(DIRECTORY_SEPARATOR, [$currentWorkingDirectory, 'config']);
        if (! is_dir($configDirectory)) {
            return;
        }

        if (is_readable(implode(DIRECTORY_SEPARATOR, [$configDirectory,'ghostwriter', 'config.php']))) {
            $service->mergeDirectory($configDirectory);
        }
    }
}

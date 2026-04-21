<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Container;

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;
use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;
use Override;
use Throwable;

use const DIRECTORY_SEPARATOR;

use function assert;
use function class_exists;
use function dirname;
use function getcwd;
use function implode;
use function interface_exists;
use function is_a;
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

        $service->mergeDirectory(dirname($configDirectory));

        return;
        $containerConfiguration = $service->wrap('ghostwriter.container');

        foreach ($containerConfiguration->get('alias', []) as $alias => $service) {
            if (! class_exists($alias) && ! interface_exists($alias)) {
                continue;
            }
            if (! class_exists($service) && ! interface_exists($service)) {
                continue;
            }
            $container->alias($alias, $service);
        }

        foreach ($containerConfiguration->get('extend', []) as $service => $extensions) {
            if (! class_exists($service) && ! interface_exists($service)) {
                continue;
            }
            foreach ($extensions as $extension) {
                if (! is_a($extension, ExtensionInterface::class, true)) {
                    continue;
                }
                $container->extend($service, $extension);
            }
        }
        foreach ($containerConfiguration->get('factory', []) as $service => $factory) {
            if (! class_exists($service) && ! interface_exists($service)) {
                continue;
            }
            if (! is_a($factory, FactoryInterface::class, true)) {
                continue;
            }
            $container->factory($service, $factory);
        }
    }
}

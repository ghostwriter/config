<?php

declare(strict_types=1);

namespace Tests\Fixture\Autoload;

use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\ExtensionInterface;

final class ConfiguredExtension implements ExtensionInterface
{
    public function __invoke(ContainerInterface $container, object $service): void { }
}

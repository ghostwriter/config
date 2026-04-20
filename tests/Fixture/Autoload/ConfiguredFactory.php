<?php

declare(strict_types=1);

namespace Tests\Fixture\Autoload;


use Ghostwriter\Container\Interface\ContainerInterface;
use Ghostwriter\Container\Interface\Service\FactoryInterface;

final class ConfiguredFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container): object
    {
        return new ConfiguredFactoryService();
    }
}

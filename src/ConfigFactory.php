<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Contract\ConfigFactoryInterface;
use Ghostwriter\Config\Contract\Exception\ConfigExceptionInterface;
use InvalidArgumentException;

final class ConfigFactory implements ConfigFactoryInterface
{
    public function create(array $options = []): Config
    {
        return new Config($options);
    }

    public function createFromPath(string $path, ?string $key = null): Config
    {
        /**
         * @psalm-suppress UnresolvableInclude
         *
         * @var ?array $options
         */
        $options = require $path;

        if (! is_array($options)) {
            $this->throwInvalidPathException($path);
        }

        /** @var array $options */
        return match (true) {
            $key === null => new Config($options),
            default => new Config([
                $key => $options,
            ]),
        };
    }

    private function throwInvalidPathException(string $path): never
    {
        throw new class(sprintf('Invalid config path: "%s".', $path)) extends InvalidArgumentException implements ConfigExceptionInterface {
        };
    }
}

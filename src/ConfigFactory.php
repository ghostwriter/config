<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Contract\ConfigFactoryInterface;
use Ghostwriter\Config\Contract\Exception\ConfigExceptionInterface;
use InvalidArgumentException;

final class ConfigFactory implements ConfigFactoryInterface
{
    public function create(array $options): Config
    {
        return new Config($options);
    }

    public function createFromPath(string $path, ?string $root = null): Config
    {
        /**
         * @psalm-suppress UnresolvableInclude
         *
         * @var ?array $options
         */
        $options = require $path;
        if (! is_array($options)) {
            $this->raiseInvalidPathException($path);
        }

        /** @var array $options */
        return match (true) {
            $root === null => new Config($options),
            default => new Config([
                $root => $options,
            ]),
        };
    }

    private function raiseInvalidPathException(string $path): void
    {
        throw new class(sprintf(
            'Invalid config path: "%s".',
            $path
        )) extends InvalidArgumentException implements ConfigExceptionInterface {
        };
    }
}

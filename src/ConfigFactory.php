<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\InvalidConfigFileException;

final class ConfigFactory implements FactoryInterface
{
    /**
     * Create a new configuration.
     *
     * @template TCreate
     *
     * @param array<string,TCreate>|non-empty-array<string,TCreate> $options
     */
    public function create(array $options = []): Config
    {
        return new Config($options);
    }

    /**
     * @template TCreateFromPath
     *
     * @throws ConfigFileNotFoundException
     * @throws InvalidConfigFileException
     */
    public function createFromPath(string $path, ?string $key = null): Config
    {
        if (! is_file($path)) {
            throw new ConfigFileNotFoundException($path);
        }

        /** @var array<string,TCreateFromPath>|TCreateFromPath $options */
        $options = require $path;

        if (! is_array($options)) {
            throw new InvalidConfigFileException($path);
        }

        if ($key !== null) {
            /** @var array<string,TCreateFromPath> $options */
            $options = [
                $key => $options,
            ];
        }

        /** @var array<string,TCreateFromPath> $options */
        return new Config($options);
    }
}

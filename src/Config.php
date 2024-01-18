<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\Interface\ConfigInterface;
use function array_key_exists;
use function array_pop;
use function array_shift;
use function explode;
use function is_array;
use function is_file;
use function str_contains;

final class Config implements ConfigInterface
{
    /**
     * Create a new configuration.
     *
     * @template TOption
     *
     * @param array<string,TOption> $options
     */
    public function __construct(
        private array $options = []
    ) {
    }

    /**
     * @template TGet
     * @template TDefault
     *
     * @param TDefault $default
     *
     * @return TDefault|TGet
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        $options = &$this->options;
        foreach (explode('.', $key) as $index) {
            if (! is_array($options) || ! array_key_exists($index, $options)) {
                /** @var TDefault $options */
                return $default;
            }

            /** @var array<string,TGet>|TGet $options */
            $options = &$options[$index];
        }

        /** @var TDefault|TGet $options */
        return $options ?? $default;
    }

    /**
     * @template THas
     */
    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return array_key_exists($key, $this->options);
        }

        $options = &$this->options;

        foreach (explode('.', $key) as $index) {
            if (! is_array($options) || ! array_key_exists($index, $options)) {
                return false;
            }

            /** @var array<string,THas>|THas $options */
            $options = &$options[$index];
        }

        return true;
    }

    /**
     * @template TRemove
     */
    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);

            return;
        }

        $options = &$this->options;
        $indexes = explode('.', $key);
        $key = array_pop($indexes);

        while ($index = array_shift($indexes)) {
            /** @var array<string,TRemove> $options */
            $options = &$options[$index];
        }

        /** @var array<string,TRemove> $options */
        unset($options[$key]);
    }

    /**
     * @template TSet
     *
     * @param TSet $value
     */
    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->options[$key] = $value;

            return;
        }

        $options = &$this->options;

        $indexes = explode('.', $key);
        while ($index = array_shift($indexes)) {
            /** @var array<string,TSet> $options */
            $options = &$options[$index];
        }

        /** @var TSet $options */
        $options = $value;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Create a new configuration.
     *
     * @template TNew
     *
     * @throws ConfigFileNotFoundException
     * @throws InvalidConfigFileException
     */
    public static function fromPath(string $path, ?string $key = null): self
    {
        if (! is_file($path)) {
            throw new ConfigFileNotFoundException($path);
        }

        /** @var array<string,TNew>|TNew $options */
        $options = require $path;

        if (! is_array($options)) {
            throw new InvalidConfigFileException($path);
        }

        if ($key !== null) {
            /** @var array<string,TNew> $options */
            $options = [
                $key => $options,
            ];
        }

        /** @var array<string,TNew> $options */
        return new self($options);
    }
}

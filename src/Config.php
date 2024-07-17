<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Exception\ConfigFileNotFoundException;
use Ghostwriter\Config\Exception\InvalidConfigFileException;
use Ghostwriter\Config\Interface\ConfigInterface;
use Override;

use function array_key_exists;
use function array_pop;
use function array_shift;
use function basename;
use function explode;
use function is_array;
use function is_file;
use function str_contains;

/**
 * @template TKey of string
 * @template TValue
 *
 * @implements ConfigInterface<TKey,TValue>
 */
final class Config implements ConfigInterface
{
    /**
     * @param array<TKey,TValue> $options
     */
    public function __construct(
        private array $options = []
    ) {
    }

    /**
     * @template TGet of string
     * @template TDefault
     *
     * @param TGet     $key
     * @param TDefault $default
     *
     * @return TDefault|TValue
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->options)) {
            /** @var TValue */
            return $this->options[$key];
        }

        $current = $this->options;
        foreach (explode('.', $key) as $index) {
            if (! is_array($current) || ! array_key_exists($index, $current)) {
                /** @var TDefault */
                return $default;
            }

            /** @var TValue $current */
            $current = $current[$index];
        }

        return $current;
    }

    /**
     * @template THas of string
     *
     * @param THas $key
     */
    #[Override]
    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return array_key_exists($key, $this->options);
        }

        $options = $this->options;

        foreach (explode('.', $key) as $index) {
            if (! is_array($options) || ! array_key_exists($index, $options)) {
                return false;
            }

            /** @var array<THas,TValue>|TValue $options */
            $options = $options[$index];
        }

        return $options !== null;
    }

    /**
     * @template TRemove of string
     *
     * @param TRemove $key
     */
    #[Override]
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
            /** @var array<TRemove,TValue> $options */
            $options = &$options[$index];
        }

        /** @var array<TRemove,TValue> $options */
        unset($options[$key]);
    }

    /**
     * @template TSet of string
     * @template TSetValue
     *
     * @param TSet      $key
     * @param TSetValue $value
     *
     * @psalm-this-out self<TSet|TKey,TValue|TSetValue>
     */
    #[Override]
    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->options[$key] = $value;
            return;
        }

        $options = &$this->options;

        $indexes = explode('.', $key);

        while ($index = array_shift($indexes)) {
            /**
             * @var TSet                  $index
             * @var array<TSet,TSetValue> $options
             */
            $options = &$options[$index];
        }

        /** @var TSetValue $options */
        $options = $value;
    }

    /**
     * @return array<TKey,TValue>
     */
    #[Override]
    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * @template TPathKey of string
     * @template TPathValue
     *
     * @param TPathKey $path
     *
     * @throws ConfigFileNotFoundException
     * @throws InvalidConfigFileException
     */
    public static function fromPath(string $path): self
    {
        if (! is_file($path)) {
            throw new ConfigFileNotFoundException($path);
        }

        /** @var array<TPathValue> $options */
        $options = require $path;

        if (! is_array($options)) {
            throw new InvalidConfigFileException($path);
        }

        /** @var TPathKey $key */
        $key = basename($path, '.php');

        /** @var array<TPathKey,TPathValue> $options */
        $options = [
            $key => $options,
        ];

        return new self($options);
    }

    /**
     * @template TNewKey of string
     * @template TNewValue
     *
     * @param array<TNewKey,TNewValue> $options
     */
    public static function new(array $options): self
    {
        return new self($options);
    }
}

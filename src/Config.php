<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Contract\ConfigInterface;

use Traversable;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function str_contains;

final class Config implements ConfigInterface
{
    /**
     * Create a new configuration.
     */
    public function __construct(
        private array $options = []
    ) {
    }

    public function append(string $key, mixed $value): void
    {
        /** @var ?array $current */
        $current = $this->get($key, []);
        $this->set($key, [...(array) $current, ...(array) $value]);
    }

    public function prepend(string $key, mixed $value): void
    {
        /** @var ?array $current */
        $current = $this->get($key, []);
        $this->set($key, [...(array) $value, ...(array) $current]);
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (! str_contains($key, '.')) {
            return $this->options[$key] ?? $default;
        }

        $options = &$this->options;
        foreach (explode('.', $key) as $index) {
            /** @var array<array-key,mixed> $options */
            if (! array_key_exists($index, $options)) {
                return $default;
            }
            $options = &$options[$index];
        }

        return $options;
    }

    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return array_key_exists($key, $this->options);
        }

        $options = &$this->options;
        foreach (explode('.', $key) as $index) {
            /** @var array<array-key,mixed> $options */
            if (! array_key_exists($index, $options)) {
                return false;
            }

            $options = &$options[$index];
        }

        return true;
    }

    public function mergeConfig(self $config): void
    {
        $this->options = array_merge($config->toArray(), $this->options);
    }

    /**
     * Merge the given configuration options, overriding existing values.
     */
    public function merge(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Merge the given configuration without overriding existing values.
     */
    public function mergeFromPath(string $path, string $key): void
    {
        $this->set($key, array_merge(require $path, $this->get($key, [])));
    }

    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->options[$key] = $value;
            return;
        }

        $options = &$this->options;
        $indexes = explode('.', $key);
        $size = count($indexes);
        while ($size > 1) {
            --$size;
            $index = array_shift($indexes);
            $options[$index] ??= [];
            $options = &$options[$index];
        }

        $options[array_shift($indexes)] = $value;
    }

    public function remove(string $key): void
    {
        if (! str_contains($key, '.')) {
            unset($this->options[$key]);

            return;
        }

        $options = &$this->options;
        $indexes = explode('.', $key);
        $size = count($indexes);
        while ($size > 1) {
            --$size;
            $index = array_shift($indexes);
            $options[$index] ??= [];
            $options = &$options[$index];
        }
        unset($options[array_shift($indexes)]);
    }

    public function count(): int
    {
        return count($this->options);
    }

    public function getIterator(): Traversable
    {
        yield from $this->options;
    }

    public function split(string $key): ConfigInterface
    {
        /** @var array $iterable */
        $iterable = $this->get($key);
        return new self($iterable);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        //        $this->remove($offset);
        $this->set($offset, null);
    }
}

<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Contract\ConfigInterface;

use function array_merge;
use function array_shift;
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

    public function count(): int
    {
        return count($this->options);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return match (true) {
            array_key_exists($key, $this->options) => $this->options[$key],
            str_contains($key, '.') => $this->find($key),
            default => $default
        };
    }

    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return array_key_exists($key, $this->options);
        }

        $options = $this->options;

        foreach (explode('.', $key) as $index) {
            if (! is_array($options)) {
                return false;
            }

            if (! array_key_exists($index, $options)) {
                return false;
            }

            /** @var array|mixed $options */
            $options = $options[$index];
        }

        return true;
    }

    /**
     * Merge the given configuration; overriding existing values.
     */
    public function join(array $options, ?string $key = null): void
    {
        if (null === $key) {
            $this->options = array_merge($this->options, $options);

            return;
        }

        /** @var array $current */
        $current = $this->get($key, []);
        $this->set($key, array_merge($current, $options));
    }

    /**
     * Merge the given configuration options without overriding existing values.
     */
    public function merge(array $options, ?string $key = null): void
    {
        if (null === $key) {
            $this->options = array_merge($options, $this->options);

            return;
        }

        /** @var array $current */
        $current = $this->get($key, []);
        $this->set($key, array_merge($options, $current));
    }

    public function offsetExists(mixed $offset): bool
    {
        /** @var string $offset */
        return $this->has($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        /** @var string $offset */
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @var string $offset */
        $this->set($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        /** @var string $offset */
        $this->remove($offset);
    }

    public function prepend(string $key, mixed $value): void
    {
        /** @var ?array $current */
        $current = $this->get($key, []);
        $this->set($key, [...(array) $value, ...(array) $current]);
    }

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
            /** @var array<string,mixed> $options */
            $options = &$options[$index];
        }

        /** @var array $options */
        unset($options[$key]);
    }

    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->options[$key] = $value;

            return;
        }

        $options = &$this->options;
        $indexes = explode('.', $key);

        while ($index = array_shift($indexes)) {
            /** @var array<string,mixed> $options */
            $options = &$options[$index];
        }

        /** @var ?mixed $options */
        $options = $value;
    }

    public function toArray(): array
    {
        return $this->options;
    }

    public function wrap(string $key): self
    {
        /** @var array|mixed $value */
        $value = $this->get($key);

        return new self(match (true) {
            is_array($value) => $value,
            default => [$value]
        });
    }

    private function find(string $key): mixed
    {
        $options = &$this->options;

        foreach (explode('.', $key) as $index) {
            if (! is_array($options)) {
                return null;
            }

            if (! array_key_exists($index, $options)) {
                return null;
            }

            $options = &$options[$index];
        }

        return $options;
    }
}

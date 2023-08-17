<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use function array_merge;
use function array_shift;
use function explode;
use function str_contains;

final class Config implements ConfigInterface
{
    /**
     * Create a new configuration.
     *
     * @template TValue
     *
     * @param array<string,TValue>|non-empty-array<string,TValue> $options
     */
    public function __construct(
        private array $options = []
    ) {
    }

    /**
     * @template TAppend
     * @template TAppendValue
     *
     * @param TAppendValue $value
     */
    public function append(string $key, mixed $value): void
    {
        $current = $this->get($key, []);

        $this->set($key, array_merge((array) $current, (array) $value));
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        return count($this->options);
    }

    /**
     * @template TGet
     * @template TGetDefault
     *
     * @param TGetDefault $default
     *
     * @return TGet|TGetDefault
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
        $options = &$this->options;
        foreach (explode('.', $key) as $index) {
            if (! is_array($options) || ! array_key_exists($index, $options)) {
                /** @var TGetDefault $options */
                return $default;
            }

            /** @var array<string,TGet>|TGet $options */
            $options = &$options[$index];
        }

        /** @var TGet|TGetDefault $options */
        return $options ?? $default;
    }

    /**
     * @template THas
     *
     * @psalm-assert true isset($this[$key])
     */
    public function has(string $key): bool
    {
        if (! str_contains($key, '.')) {
            return array_key_exists($key, $this->options);
        }

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
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
     * Merge the given configuration; overriding existing values.
     *
     * @template TJoin
     *
     * @param array<string,TJoin>|non-empty-array<string,TJoin> $options
     */
    public function join(array $options, ?string $key = null): void
    {
        if ($key === null) {
            $this->options = array_merge($this->options, $options);

            return;
        }

        /** @var non-empty-array<string,TJoin> $current */
        $value = array_merge($this->get($key, []), $options);

        $this->set($key, $value);
    }

    /**
     * Merge the given configuration options without overriding existing values.
     *
     * @template TMerge
     *
     * @param array<string,TMerge>|non-empty-array<string,TMerge> $options
     */
    public function merge(array $options, ?string $key = null): void
    {
        if ($key === null) {
            $this->options = array_merge($options, $this->options);

            return;
        }

        /** @var non-empty-array<string,TMerge> $current */
        $value = array_merge($options, $this->get($key, []));

        $this->set($key, $value);
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
        $this->remove($offset);
    }

    /**
     * @template TPrepend
     * @template TPrependValue
     *
     * @param TPrependValue $value
     */
    public function prepend(string $key, mixed $value): void
    {
        /** @var non-empty-array<string,TPrepend> $value */
        $value = array_merge((array) $value, (array) $this->get($key, []));

        $this->set($key, $value);
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

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
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
     * @template TSetValue
     *
     * @param TSetValue $value
     */
    public function set(string $key, mixed $value): void
    {
        if (! str_contains($key, '.')) {
            $this->options[$key] = $value;

            return;
        }

        /** @psalm-suppress UnsupportedPropertyReferenceUsage */
        $options = &$this->options;

        $indexes = explode('.', $key);
        while ($index = array_shift($indexes)) {
            /** @var array<string,TSet> $options */
            $options = &$options[$index];
        }

        /** @var TSet $options */
        $options = $value;
    }

    /**
     * @template TArray
     *
     * @return array<string,TArray>|non-empty-array<string,TArray>
     */
    public function toArray(): array
    {
        return $this->options;
    }

    public function wrap(string $key): self
    {
        return new self([
            $key => $this->get($key),
        ]);
    }
}

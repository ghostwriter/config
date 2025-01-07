<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Exception\EmptyConfigKeyException;
use Ghostwriter\Config\Exception\InvalidConfigKeyException;
use Override;

use function array_key_exists;
use function array_map;
use function array_merge_recursive;
use function array_pop;
use function array_shift;
use function explode;
use function implode;
use function is_array;
use function is_string;
use function mb_trim;
use function str_contains;

final class Config implements ConfigInterface
{
    /**
     * @var array<string,mixed>
     */
    private array $options = [];

    /**
     * @param array<string,mixed> $options
     *
     * @throws EmptyConfigKeyException
     * @throws InvalidConfigKeyException
     */
    public function __construct(
        array $options = [],
    ) {
        /** @var array<array-key,mixed> $options */
        foreach ($options as $key => $value) {
            if (! is_string($key)) {
                throw new InvalidConfigKeyException('Config key must be a non-empty-string');
            }

            $this->set($key, $value);
        }
    }

    /**
     * @param array<string,mixed> $options
     *
     * @throws EmptyConfigKeyException
     * @throws InvalidConfigKeyException
     */
    public static function new(array $options = []): self
    {
        return new self($options);
    }

    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        if (str_contains($key, '.')) {
            return self::getRecursively($this->options, $default, ...explode('.', $key));
        }

        return $this->options[$key] ?? $default;
    }

    #[Override]
    public function has(string $key): bool
    {
        if (str_contains($key, '.')) {
            return $this->hasRecursively($this->options, ...explode('.', $key));
        }

        return array_key_exists($key, $this->options);
    }

    /**
     * @param array<string,mixed> $config
     */
    #[Override]
    public function merge(array $config): self
    {
        $this->options = array_merge_recursive($this->options, $config);

        return $this;
    }

    #[Override]
    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->options)) {
            unset($this->options[$key]);

            return;
        }

        $options = &$this->options;

        /** @var list<string> $indexes */
        $indexes = explode('.', $key);

        $last = array_pop($indexes);

        while ([] !== $indexes) {
            $index = array_shift($indexes);

            $options = &$options[$index];
        }

        /** @var array<string,mixed> $options */
        unset($options[$last]);
    }

    /**
     * @throws EmptyConfigKeyException
     */
    #[Override]
    public function set(string $key, mixed $value): void
    {
        if ('' === mb_trim($key)) {
            throw new EmptyConfigKeyException();
        }

        $this->setRecursively($this->options, $value, ...explode('.', $key));
    }

    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $value): mixed => $value instanceof ConfigInterface ? $value->toArray() : $value,
            $this->options,
        );
    }

    private function hasRecursively(array $options, string ...$keys): bool
    {
        $last = array_pop($keys);

        foreach ($keys as $key) {
            if (! array_key_exists($key, $options)) {
                return false;
            }

            /** @var array<string,mixed>|mixed $options */
            $options = $options[$key];

            if (! is_array($options)) {
                return false;
            }
        }

        return array_key_exists($last, $options);
    }

    private function setRecursively(array &$config, mixed $value, string ...$keys): void
    {
        $current = &$config;

        foreach ($keys as $key) {
            if ('' === mb_trim($key)) {
                throw new InvalidConfigKeyException(implode('.', $keys));
            }

            if (! array_key_exists($key, $current)) {
                $current[$key] = [];
            }

            $current = &$current[$key];
        }

        $current = $value;
    }

    private static function getRecursively(array $config, mixed $default, string ...$keys): mixed
    {
        $key = array_shift($keys);

        if (! array_key_exists($key, $config)) {
            return $default;
        }

        if ([] === $keys) {
            return $config[$key] ?? $default;
        }

        $value = $config[$key];

        if (is_array($value)) {
            return self::getRecursively($value, $default, ...$keys);
        }

        return $default;
    }
}

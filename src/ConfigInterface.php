<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use ArrayAccess;
use Countable;

/**
 * @extends ArrayAccess<string,mixed>
 */
interface ConfigInterface extends ArrayAccess, Countable
{
    public function append(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    /**
     * @template TJoin
     *
     * @param non-empty-array<string,TJoin> $options
     */
    public function join(array $options, ?string $key = null): void;

    /**
     * @template TMerge
     *
     * @param non-empty-array<string,TMerge> $options
     */
    public function merge(array $options, ?string $key = null): void;

    public function prepend(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function set(string $key, mixed $value): void;

    /**
     * @template TArray
     *
     * @return array<string,TArray>|non-empty-array<string,TArray>
     */
    public function toArray(): array;

    public function wrap(string $key): self;
}

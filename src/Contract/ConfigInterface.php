<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Contract;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<string,mixed>
 */
interface ConfigInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function toArray(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function merge(array $options): void;

    /**
     * Merge the given configuration with the existing configuration.
     */
    public function mergeFromPath(string $path, string $key): void;

    public function set(string $key, mixed $value): void;

    public function push(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function count(): int;

    public function getIterator(): Traversable;

    public function append(string $key, mixed $value): void;

    public function prepend(string $key, mixed $value): void;

    public function split(string $key): self;
}

<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Contract;

use ArrayAccess;
use Countable;

/**
 * @extends ArrayAccess<array-key,mixed>
 */
interface ConfigInterface extends ArrayAccess, Countable
{
    /** @return array<array-key,mixed> */
    public function toArray(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function join(array $options, ?string $key = null): void;

    public function merge(array $options, ?string $key = null): void;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function append(string $key, mixed $value): void;

    public function prepend(string $key, mixed $value): void;

    public function wrap(string $key): self;
}

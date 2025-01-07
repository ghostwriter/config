<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    /**
     * @param array<string,mixed> $config
     */
    public function merge(array $config): self;

    public function remove(string $key): void;

    public function set(string $key, mixed $value): void;

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array;
}

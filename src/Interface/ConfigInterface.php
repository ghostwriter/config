<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Interface;

interface ConfigInterface
{
    /**
     * @template TDefault
     * @template TGet
     *
     * @param TDefault $default
     *
     * @return TDefault|TGet
     */
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function remove(string $key): void;

    /**
     * @template TSet
     *
     * @param TSet $value
     */
    public function set(string $key, mixed $value): void;

    public function toArray(): array;
}

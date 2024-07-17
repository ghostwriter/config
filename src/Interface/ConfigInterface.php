<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Interface;

/**
 * @template TKey of string
 * @template TValue
 */
interface ConfigInterface
{
    /**
     * @template TGet of string
     * @template TDefault
     *
     * @param TGet     $key
     * @param TDefault $default
     *
     * @return TDefault|TValue
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * @template THas of string
     *
     * @param THas $key
     */
    public function has(string $key): bool;

    /**
     * @template TRemove of string
     *
     * @param TRemove $key
     */
    public function remove(string $key): void;

    /**
     * @template TSet of string
     * @template TSetValue
     *
     * @param TSet      $key
     * @param TSetValue $value
     *
     * @psalm-this-out self<TSet|TKey,TValue|TSetValue>
     */
    public function set(string $key, mixed $value): void;

    /**
     * @return array<TKey,TValue>
     */
    public function toArray(): array;
}

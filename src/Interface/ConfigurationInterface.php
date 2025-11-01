<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Interface;

/**
 * @template T of (array<non-empty-string,T>|bool|float|int|null|string)
 */
interface ConfigurationInterface
{
    /** @throws ConfigurationExceptionInterface */
    public function append(string $key, mixed $value): void;

    /** @throws ConfigurationExceptionInterface */
    public function get(string $key, mixed $default = null): mixed;

    /** @throws ConfigurationExceptionInterface */
    public function has(string $key): bool;

    /**
     * @param array<non-empty-string,T> $options
     *
     * @throws ConfigurationExceptionInterface
     */
    public function merge(array $options): void;

    /** @throws ConfigurationExceptionInterface */
    public function mergeDirectory(string $directory): void;

    /** @throws ConfigurationExceptionInterface */
    public function mergeFile(string $file, ?string $key = null): void;

    /** @throws ConfigurationExceptionInterface */
    public function prepend(string $key, mixed $value): void;

    public function reset(): void;

    /** @throws ConfigurationExceptionInterface */
    public function set(string $key, mixed $value): void;

    /** @return array<non-empty-string,T> */
    public function toArray(): array;

    /** @throws ConfigurationExceptionInterface */
    public function unset(string $key): void;

    /**
     * @param array<non-empty-string,T> $default
     *
     * @throws ConfigurationExceptionInterface
     */
    public function wrap(string $key, array $default = []): self;
}

# Config

[![Compliance](https://github.com/ghostwriter/config/actions/workflows/compliance.yml/badge.svg)](https://github.com/ghostwriter/config/actions/workflows/compliance.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/config?color=8892bf)](https://www.php.net/supported-versions)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/config/coverage.svg)](https://shepherd.dev/github/ghostwriter/config)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/config)](https://packagist.org/packages/ghostwriter/config)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/config?color=blue)](https://packagist.org/packages/ghostwriter/config)

Provides an object that maps configuration keys to values.


## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/config
```

## Usage

```php
// API
interface ConfigInterface extends ArrayAccess, Countable, IteratorAggregate
{
    public function toArray(): array;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function merge(array $options): void;

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
```

## Testing

``` bash
composer test
```

## Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information what has changed recently.

## Security

If you discover any security related issues, please email `nathanael.esayeas@protonmail.com` instead of using the issue tracker.

## Support

[[`Become a GitHub Sponsor`](https://github.com/sponsors/ghostwriter)]

## Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/config/contributors)

## License

The BSD-3-Clause. Please see [License File](./LICENSE) for more information.

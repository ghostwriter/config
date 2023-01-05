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

$configFactory = new ConfigFactory();

$key = 'nested';
$path = 'path/to/config.php';
$options = ['settings' => ['enable' => true]];

$config = $configFactory->create($options);
$config->toArray(); // ['settings' => ['enable'=>true]]
 
$config = $configFactory->createFromPath($path);
$config->toArray(); // ['settings' => ['enable'=>true]]

$config = $configFactory->createFromPath($path, $key);
$config->toArray(); // ['nested' => ['settings' => ['enable'=>true]]]

//

$config = new Config($options);
$config->has('settings'); // true
$config->has('settings.enable'); // true
$config->get('settings.enable'); // true

$config->has('settings.disabled'); // false
$config->get('settings.disabled'); // null
$config->get('settings.disabled', 'default'); // 'default'

$config->set('settings.disabled', false); // true
$config->has('settings.disabled'); // true
$config->get('settings.disabled'); // false

$config->toArray(); // ['settings' => ['enable'=>true,'disabled'=>false]]
```

## API

```php
// API
// ConfigFactory
interface ConfigFactoryInterface
{
    public function create(array $options = []): ConfigInterface;

    public function createFromPath(string $path, ?string $key = null): ConfigInterface;
}
// Config
/** @extends ArrayAccess<array-key,mixed> */
interface ConfigInterface extends ArrayAccess, Countable
{
    public function append(string $key, mixed $value): void;

    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function join(array $options, ?string $key = null): void;

    public function merge(array $options, ?string $key = null): void;

    public function prepend(string $key, mixed $value): void;

    public function remove(string $key): void;

    public function set(string $key, mixed $value): void;

    /** @return array<array-key,mixed> */
    public function toArray(): array;

    public function wrap(string $key): self;
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

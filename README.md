# Config

[![Compliance](https://github.com/ghostwriter/config/actions/workflows/compliance.yml/badge.svg)](https://github.com/ghostwriter/config/actions/workflows/compliance.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/config?color=8892bf)](https://www.php.net/supported-versions)
[![Mutation Coverage](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fghostwriter%2Fconfig%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/ghostwriter/config/main)
[![Code Coverage](https://codecov.io/gh/ghostwriter/config/branch/main/graph/badge.svg)](https://codecov.io/gh/ghostwriter/config)
[![Type Coverage](https://shepherd.dev/github/ghostwriter/config/coverage.svg)](https://shepherd.dev/github/ghostwriter/config)
[![Latest Version on Packagist](https://badgen.net/packagist/v/ghostwriter/config)](https://packagist.org/packages/ghostwriter/config)
[![Downloads](https://badgen.net/packagist/dt/ghostwriter/config?color=blue)](https://packagist.org/packages/ghostwriter/config)

Provides an object that maps configuration keys to values.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/config
```

### Star ⭐️ this repo if you find it useful

You can also star (🌟) this repo to find it easier later.

## Usage

```php
$key = 'nested';
$path = 'path/to/config.php';
$options = [
    'settings' => [
        'enable' => true,
    ],
];

$config = $configFactory->create($options);
$config->toArray(); // ['settings' => ['enable'=>true]]

$config = Config::fromPath($path);
$config->toArray(); // ['settings' => ['enable'=>true]]

$config = Config::fromPath($path, $key);
$config->toArray(); // ['nested' => ['settings' => ['enable'=>true]]]
$config->has('nested.settings.disabled'); // true

//

$config = new Config($options);
$config->has('settings'); // true
$config->has('settings.enable'); // true
$config->get('settings.enable'); // true

$config = Config::new($options);
$config->has('settings.disabled'); // false
$config->get('settings.disabled'); // null
$config->get('settings.disabled', 'default'); // 'default'

$config->set('settings.disabled', false); // true
$config->has('settings.disabled'); // true

$config->get('settings.disabled'); // false

$config->toArray(); // ['settings' => ['enable'=>true,'disabled'=>false]]

$config->remove('settings.disabled');

$config->get('settings.disabled'); // null

$config->toArray(); // ['settings' => ['enable'=>true]]
```

## API

```php
interface ConfigInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function set(string $key, mixed $value): void;

    public function toArray(): array;
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

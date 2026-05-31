# Config

[![Automation](https://github.com/ghostwriter/config/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/config/actions/workflows/automation.yml)
[![PHP Version](https://badgen.net/packagist/php/ghostwriter/config?color=777BB4)](https://www.php.net/supported-versions)
[![Packagist Downloads](https://badgen.net/packagist/dt/ghostwriter/config?color=F28D1A)](https://packagist.org/packages/ghostwriter/config)
[![PayPal](https://img.shields.io/badge/paypal-@codepoet-0079C1?logo=data%3Aimage%2Fsvg%2Bxml%3Bbase64%2CPHN2ZyB2aWV3Qm94PSIwIDAgMjQgMjQiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI%2BPHBhdGggZD0iTTE5LjcxNSA2LjEzM2MuMjQ5LTEuODY2IDAtMy4xMS0uOTk5LTQuMjY2QzE3LjYzNC42MjIgMTUuNzIxIDAgMTMuMzA3IDBINi4yMzVjLS40MTggMC0uOTE2LjQ0NC0xIC44ODlMMi4zMjMgMjAuNjIyYzAgLjM1Ni4yNS44LjY2NS44aDQuMzI4bC0uMjUgMS45NTZjLS4wODQuMzU1LjE2Ni42MjIuNDk4LjYyMmgzLjY2M2MuNDE3IDAgLjgzMi0uMjY3LjkxNS0uNzExdi0uMjY3bC43NDktNC42MjJ2LS4xNzhjLjA4My0uNDQ0LjUtLjguOTE1LS44aC41YzMuNTc4IDAgNi4zMjUtMS41MSA3LjE1Ni01Ljk1NS40MTgtMS44NjcuMjUyLTMuMzc4LS43NDctNC40NDUtLjI1LS4zNTUtLjY2Ni0uNjIyLTEtLjg4OSIgZmlsbD0iIzAwOWNkZSIvPjxwYXRoIGQ9Ik0xOS43MTUgNi4xMzNjLjI0OS0xLjg2NiAwLTMuMTEtLjk5OS00LjI2NkMxNy42MzQuNjIyIDE1LjcyMSAwIDEzLjMwNyAwSDYuMjM1Yy0uNDE4IDAtLjkxNi40NDQtMSAuODg5TDIuMzIzIDIwLjYyMmMwIC4zNTYuMjUuOC42NjUuOGg0LjMyOGwxLjE2NC03LjM3OC0uMDgzLjI2N2MuMDg0LS41MzMuNS0uODg5Ljk5OC0uODg5aDIuMDhjNC4wNzkgMCA3LjI0MS0xLjc3OCA4LjI0LTYuNzU1LS4wODMtLjI2NyAwLS4zNTYgMC0uNTM0IiBmaWxsPSIjMDEyMTY5Ii8%2BPHBhdGggZD0iTTkuNTYzIDYuMTMzYy4wODItLjI2Ni4yNS0uNTMzLjQ5OC0uNzEuMTY2IDAgLjI1LS4wOS40MTYtLjA5aDUuNDk0Yy42NjYgMCAxLjMzLjA5IDEuODMuMTc4LjE2NiAwIC4zMzMgMCAuNDk4LjA4OS4xNjguMDg5LjMzNC4wODkuNDE4LjE3OGguMjVjLjI0OC4wODkuNDk3LjI2Ni43NDguMzU1LjI0OC0xLjg2NiAwLTMuMTEtLjk5OS00LjM1NUMxNy43MTcuNTMzIDE1LjgwNCAwIDEzLjM5IDBINi4yMzVjLS40MTggMC0uOTE2LjM1Ni0xIC44ODlMMi4zMjMgMjAuNjIyYzAgLjM1Ni4yNS44LjY2NS44aDQuMzI4bDEuMTY0LTcuMzc4IDEuMDg0LTcuOTF6IiBmaWxsPSIjMDAzMDg3Ii8%2BPC9zdmc%2B)](https://paypal.me/codepoet)
[![Sponsors via GitHub](https://img.shields.io/github/sponsors/ghostwriter?label=Sponsor+@ghostwriter/config&logo=GitHub+Sponsors)](https://github.com/sponsors/ghostwriter)

Provides an object that maps configuration keys to values.

## Installation

You can install the package via composer:

``` bash
composer require ghostwriter/config
```

### Star ⭐️ this repo if you find it useful

You can also star (🌟) this repo to find it easier later.

## Features

- 🔑 **Dot Notation**: Access nested configuration using dot, forward slash, or backslash separators
- 🔄 **Flexible Merging**: Merge arrays, files, or directories
- 🔧 **Array Operations**: Append and prepend values to configuration arrays
- 🎯 **Scoped Configuration**: Wrap nested configuration into isolated instances

## API

```php
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
     * @throws ConfigurationExceptionInterface
     */
    public function wrap(string $key, array $default = []): self;
}
```

## Usage

Given the following configuration directory structure

- `path/to/config/directory/app.php`
- `path/to/config/directory/database.php`
- `path/to/config/directory/file.php`

```php
$directory = 'path/to/config/directory';
$file = 'path/to/config/directory/file.php';
$options = [
    'settings' => [
        'enable' => true,
    ],
];

$configuration = Configuration::new($options);
$configuration->has('settings.disabled'); // false
$configuration->get('settings.disabled'); // null
$configuration->get('settings.disabled', 'default'); // 'default'

$configuration->set('settings.disabled', false);

$configuration->has('settings.disabled'); // true
$configuration->get('settings.disabled'); // false

$configuration->toArray(); // ['settings' => ['enable'=>true,'disabled'=>false]]

$configuration->unset('settings.disabled');

$configuration->get('settings.disabled'); // null
$configuration->get('settings.disabled', 'default'); // 'default'

$configuration->toArray(); // ['settings' => ['enable'=>true]]
```

```php
// from an array
$configuration = Configuration::new($options); 
$configuration->toArray(); // ['settings' => ['enable'=>true]]
```

```php
// merge additional config options
$additionalOptions = [
    'settings' => [
        'disabled' => false,
    ],
];
$configuration = Configuration::new($options); 
$configuration->merge($additionalOptions);
$configuration->toArray(); // ['settings' => ['enable'=>true,'disabled'=>false]]
```

```php
// from an array with dot notation
$configuration = Configuration::new($options); 
$configuration->toArray(); // ['settings' => ['enable'=>true]]

$configuration->has('settings'); // true
$configuration->has('settings.enable'); // true
$configuration->get('settings.enable'); // true
```

```php
// from a directory
$configuration = Configuration::new();

$configuration->mergeDirectory($directory);

$configuration->toArray(); // output below
// [
//      'app' => ['name'=>'App','version'=>'1.0.0'],
//      'database' => ['host'=>'localhost','port'=>3306],
//      'file' => ['path'=>'/path/to/file']
// ]
```

```php
// from a file
$configuration = Configuration::new();
$configuration->mergeFile($file);
$configuration->toArray(); // ['path'=>'/path/to/file']

// from a file with a namespace key
$configuration = Configuration::new();
$configuration->mergeFile($file, 'custom');
$configuration->toArray(); // ['custom' => ['path'=>'/path/to/file']]
```

```php
// append values
$configuration = Configuration::new($options); 
$configuration->append('settings', ['key' => 'value']);
$configuration->toArray(); // ['settings' => ['enable'=>true,'key'=>'value']]
```

```php
// prepend values
$configuration = Configuration::new($options); 
$configuration->prepend('settings', ['key' => 'value']);
$configuration->toArray(); // ['settings' => ['key'=>'value','enable'=>true]]
```

```php
// wrap configuration into a new scoped instance
$configuration = Configuration::new([
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
    ],
]);

$dbConfig = $configuration->wrap('database');
$dbConfig->get('host'); // 'localhost'
$dbConfig->toArray(); // ['host' => 'localhost', 'port' => 3306]

// wrap with default values when key doesn't exist
$cacheConfig = $configuration->wrap('cache', ['driver' => 'redis']);
$cacheConfig->toArray(); // ['driver' => 'redis']
```

```php
// reset configuration
$configuration = Configuration::new(['key' => 'value']);
$configuration->toArray(); // ['key' => 'value']

$configuration->reset();
$configuration->toArray(); // []
```

```php
// mixed separators support (dot, forward slash, backslash)
$configuration = Configuration::new();
$configuration->set('app.name', 'MyApp');
$configuration->get('app.name'); // 'MyApp'
$configuration->get('app/name'); // 'MyApp' (forward slash)
$configuration->get('app\name'); // 'MyApp' (backslash)

$configuration->has('app.name'); // true
$configuration->has('app/name'); // true
$configuration->has('app\name'); // true
```

```php
// append/prepend to nested keys
$configuration = Configuration::new();
$configuration->append('list.items', 'first');
$configuration->append('list.items', 'second');
$configuration->toArray(); // ['list' => ['items' => ['first', 'second']]]

$configuration->prepend('list.items', 'zero');
$configuration->toArray(); // ['list' => ['items' => ['zero', 'first', 'second']]]
```

```php
// automatic array promotion
$configuration = Configuration::new();
$configuration->set('parent', 'scalar');
$configuration->set('parent.child', 'value'); // 'parent' is promoted to array
$configuration->toArray(); // ['parent' => ['child' => 'value']]
```

### Changelog

Please see [CHANGELOG.md](./CHANGELOG.md) for more information on what has changed recently.

### Credits

- [Nathanael Esayeas](https://github.com/ghostwriter)
- [All Contributors](https://github.com/ghostwriter/config/contributors)

### License

Please see [LICENSE](./LICENSE) for more information on the license that applies to this project.

### Security

Please see [SECURITY.md](./SECURITY.md) for more information on security disclosure process.

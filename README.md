# Config

[![Automation](https://github.com/ghostwriter/config/actions/workflows/automation.yml/badge.svg)](https://github.com/ghostwriter/config/actions/workflows/automation.yml)
[![Supported PHP Version](https://badgen.net/packagist/php/ghostwriter/config?color=8892bf)](https://www.php.net/supported-versions)
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

```php
// from an array
$configFactory = ConfigFactory::new(); // or new ConfigFactory()
$config = $configFactory->create($options); 
$config->toArray(); // ['settings' => ['enable'=>true]]

$config->has('settings'); // true
$config->has('settings.enable'); // true
$config->get('settings.enable'); // true
```

```php
// from a directory
$configFactory = ConfigFactory::new(); // or new ConfigFactory()
$config = $configFactory->createFromDirectory($options);
$config->toArray(); // output below
// [
//      'app' => ['name'=>'App','version'=>'1.0.0'],
//      'database' => ['host'=>'localhost','port'=>3306]
//      'file' => ['path'=>'/path/to/file']
// ]
```

```php
// from a file
$configFactory = ConfigFactory::new(); // or new ConfigFactory()
$config = $configFactory->createFromFile($file);
$config->toArray(); // ['path'=>'/path/to/file']
```

### API

```php
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
```

```php
interface ConfigFactoryInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function create(array $config = []): ConfigInterface;

    public function createFromDirectory(string $directory): ConfigInterface;

    public function createFromFile(string $file): ConfigInterface;
}
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

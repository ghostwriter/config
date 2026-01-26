--TEST--
Test for Ghostwriter\Config\Configuration::has()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;

$options = [
    'settings' => [
        'enable' => true,
    ],
];

$configuration = Configuration::new($options);

var_dump($configuration->has('settings'));
var_dump($configuration->has('settings.enable'));
var_dump($configuration->has('settings.disabled'));
var_dump($configuration->has('nonexistent'));

echo 'OK';

?>
--EXPECT--
bool(true)
bool(true)
bool(false)
bool(false)
OK

--TEST--
Test for Ghostwriter\Config\Configuration::get()
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

var_dump($configuration->get('settings.enable'));
var_dump($configuration->get('settings.disabled'));
var_dump($configuration->get('settings.disabled', 'default'));
var_dump($configuration->get('nonexistent.key', 42));

echo 'OK';

?>
--EXPECT--
bool(true)
NULL
string(7) "default"
int(42)
OK

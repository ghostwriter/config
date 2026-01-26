--TEST--
Test for Ghostwriter\Config\Configuration::set()
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

$configuration->set('settings.disabled', false);
var_dump($configuration->has('settings.disabled'));
var_dump($configuration->get('settings.disabled'));

$configuration->set('new.nested.key', 'value');
var_dump($configuration->get('new.nested.key'));

echo 'OK';

?>
--EXPECT--
bool(true)
bool(false)
string(5) "value"
OK

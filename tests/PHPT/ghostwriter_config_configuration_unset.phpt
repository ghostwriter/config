--TEST--
Test for Ghostwriter\Config\Configuration::unset()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;

$options = [
    'settings' => [
        'enable' => true,
        'disabled' => false,
    ],
];

$configuration = Configuration::new($options);

var_dump($configuration->has('settings.disabled'));

$configuration->unset('settings.disabled');

var_dump($configuration->has('settings.disabled'));
var_dump($configuration->get('settings.disabled'));
var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
bool(true)
bool(false)
NULL
array(1) {
  ["settings"]=>
  array(1) {
    ["enable"]=>
    bool(true)
  }
}
OK

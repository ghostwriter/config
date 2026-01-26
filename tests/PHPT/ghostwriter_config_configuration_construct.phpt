--TEST--
Test for Ghostwriter\Config\Configuration::__construct()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;

$options = [
    'settings' => [
        'enable' => true,
    ],
];

$configuration = Configuration::new($options);

var_dump($configuration instanceof ConfigurationInterface); // true

var_dump($configuration->has('settings.disabled')); // false

var_dump($configuration->get('settings.disabled')); // null

var_dump($configuration->get('settings.disabled', 'default')); // 'default'

$configuration->set('settings.disabled', false); // void

var_dump($configuration->has('settings.disabled')); // true
var_dump($configuration->get('settings.disabled')); // false

var_dump($configuration->toArray()); // ['settings' => ['enable'=>true,'disabled'=>false]]

$configuration->unset('settings.disabled');

var_dump($configuration->get('settings.disabled')); // null
var_dump($configuration->get('settings.disabled', 'default')); // 'default'

var_dump($configuration->toArray()); // ['settings' => ['enable'=>true]]

echo 'OK';

?>
--EXPECT--
bool(true)
bool(false)
NULL
string(7) "default"
bool(true)
bool(false)
array(1) {
  ["settings"]=>
  array(2) {
    ["enable"]=>
    bool(true)
    ["disabled"]=>
    bool(false)
  }
}
NULL
string(7) "default"
array(1) {
  ["settings"]=>
  array(1) {
    ["enable"]=>
    bool(true)
  }
}
OK

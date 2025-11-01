--TEST--
Test for Ghostwriter\Config\Configuration::reset()
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

var_dump($configuration->toArray());

$configuration->reset();

var_dump($configuration->toArray());
var_dump($configuration->has('settings'));

echo 'OK';

?>
--EXPECT--
array(1) {
  ["settings"]=>
  array(1) {
    ["enable"]=>
    bool(true)
  }
}
array(0) {
}
bool(false)
OK

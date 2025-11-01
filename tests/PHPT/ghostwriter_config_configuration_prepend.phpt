--TEST--
Test for Ghostwriter\Config\Configuration::prepend()
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
$configuration->prepend('settings', ['key' => 'value']);

var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
array(1) {
  ["settings"]=>
  array(2) {
    ["key"]=>
    string(5) "value"
    ["enable"]=>
    bool(true)
  }
}
OK

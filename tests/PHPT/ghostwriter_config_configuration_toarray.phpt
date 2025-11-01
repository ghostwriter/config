--TEST--
Test for Ghostwriter\Config\Configuration::toArray()
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
    'app' => [
        'name' => 'TestApp',
        'version' => '1.0.0',
    ],
];

$configuration = Configuration::new($options);

var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
array(2) {
  ["settings"]=>
  array(2) {
    ["enable"]=>
    bool(true)
    ["disabled"]=>
    bool(false)
  }
  ["app"]=>
  array(2) {
    ["name"]=>
    string(7) "TestApp"
    ["version"]=>
    string(5) "1.0.0"
  }
}
OK

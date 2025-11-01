--TEST--
Test for Ghostwriter\Config\Configuration::new()
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

var_dump($configuration instanceof ConfigurationInterface);
var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
bool(true)
array(1) {
  ["settings"]=>
  array(1) {
    ["enable"]=>
    bool(true)
  }
}
OK

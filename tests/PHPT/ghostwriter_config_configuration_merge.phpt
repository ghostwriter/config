--TEST--
Test for Ghostwriter\Config\Configuration::merge()
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

$additionalOptions = [
    'settings' => [
        'disabled' => false,
    ],
];

$configuration = Configuration::new($options);
$configuration->merge($additionalOptions);

var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
array(1) {
  ["settings"]=>
  array(2) {
    ["enable"]=>
    bool(true)
    ["disabled"]=>
    bool(false)
  }
}
OK

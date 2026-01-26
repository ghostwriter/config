--TEST--
Test for Ghostwriter\Config\Configuration::append()
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
$configuration->append('settings', ['key' => 'value']);
$configuration->append('settings', ['value-without-key']);
$configuration->append('settings', 'string-value-without-key');

var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
array(1) {
  ["settings"]=>
  array(4) {
    ["enable"]=>
    bool(true)
    ["key"]=>
    string(5) "value"
    [0]=>
    string(17) "value-without-key"
    [1]=>
    string(24) "string-value-without-key"
  }
}
OK

--TEST--
Test for Ghostwriter\Config\Configuration combining merge() and mergeFile()
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

$devFile = __DIR__ . '/../Fixture/config/app.php';

$configuration = Configuration::new($options);
$configuration->mergeFile($devFile, 'app');

var_dump($configuration->has('settings'));
var_dump($configuration->has('app'));
var_dump($configuration->get('settings.enable'));
var_dump($configuration->get('app.name'));

$array = $configuration->toArray();
ksort($array);
var_dump($array);

echo 'OK';

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
string(3) "App"
array(2) {
  ["app"]=>
  array(2) {
    ["name"]=>
    string(3) "App"
    ["version"]=>
    string(5) "1.0.0"
  }
  ["settings"]=>
  array(1) {
    ["enable"]=>
    bool(true)
  }
}
OK

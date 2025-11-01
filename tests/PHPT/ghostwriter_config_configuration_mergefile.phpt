--TEST--
Test for Ghostwriter\Config\Configuration::mergeFile()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;

$fixtureFile = __DIR__ . '/../Fixture/config/app.php';

$configuration = Configuration::new();
$configuration->mergeFile($fixtureFile, 'app');

var_dump($configuration->has('app'));
var_dump($configuration->has('app.name'));
var_dump($configuration->has('app.version'));

var_dump($configuration->get('app.name'));
var_dump($configuration->get('app.version'));
var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
string(3) "App"
string(5) "1.0.0"
array(1) {
  ["app"]=>
  array(2) {
    ["name"]=>
    string(3) "App"
    ["version"]=>
    string(5) "1.0.0"
  }
}
OK

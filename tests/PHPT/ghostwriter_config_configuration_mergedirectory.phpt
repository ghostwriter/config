--TEST--
Test for Ghostwriter\Config\Configuration::mergeDirectory()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;

$fixtureDirectory = __DIR__ . '/../Fixture/config/';

$configuration = Configuration::new();
$configuration->mergeDirectory($fixtureDirectory);

var_dump($configuration->has('app'));
var_dump($configuration->has('database'));
var_dump($configuration->get('app.name'));
var_dump($configuration->get('app.version'));
var_dump($configuration->get('database.host'));
var_dump($configuration->get('database.port'));

var_dump($configuration->toArray());

echo 'OK';

?>
--EXPECT--
bool(true)
bool(true)
string(3) "App"
string(5) "1.0.0"
string(9) "localhost"
int(3306)
array(2) {
  ["app"]=>
  array(2) {
    ["name"]=>
    string(3) "App"
    ["version"]=>
    string(5) "1.0.0"
  }
  ["database"]=>
  array(2) {
    ["host"]=>
    string(9) "localhost"
    ["port"]=>
    int(3306)
  }
}
OK

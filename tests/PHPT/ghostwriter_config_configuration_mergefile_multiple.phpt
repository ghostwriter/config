--TEST--
Test for Ghostwriter\Config\Configuration::mergeFile() with multiple files
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;

$appFile = __DIR__ . '/../Fixture/config/app.php';
$databaseFile = __DIR__ . '/../Fixture/config/database.php';

$configuration = Configuration::new();
$configuration->mergeFile($appFile, 'app');
$configuration->mergeFile($databaseFile, 'database');

var_dump($configuration->has('app.name'));
var_dump($configuration->has('app.version'));
var_dump($configuration->has('database.host'));
var_dump($configuration->has('database.port'));

var_dump($configuration->get('app.name'));
var_dump($configuration->get('database.host'));

$array = $configuration->toArray();
ksort($array);
var_dump($array);

echo 'OK';

?>
--EXPECT--
bool(true)
bool(true)
bool(true)
bool(true)
string(3) "App"
string(9) "localhost"
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

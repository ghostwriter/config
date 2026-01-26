--TEST--
Test for Ghostwriter\Config\Configuration::wrap()
--FILE--
<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Ghostwriter\Config\Configuration;
use Ghostwriter\Config\Interface\ConfigurationInterface;

$options = [
    'database' => [
        'host' => 'localhost',
        'port' => 3306,
    ],
    'app' => [
        'name' => 'TestApp',
    ],
];

$configuration = Configuration::new($options);

$dbConfig = $configuration->wrap('database');

var_dump($dbConfig instanceof ConfigurationInterface);
var_dump($dbConfig->toArray());
var_dump($dbConfig->get('host'));
var_dump($dbConfig->get('port'));

echo 'OK';

?>
--EXPECT--
bool(true)
array(2) {
  ["host"]=>
  string(9) "localhost"
  ["port"]=>
  int(3306)
}
string(9) "localhost"
int(3306)
OK

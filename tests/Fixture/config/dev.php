<?php

declare(strict_types=1);

return [
    'type' => 'dev',
    'BLM' => '#BlackLivesMatter',
    'bool' => [
        'true' => true,
        'false' => false,
    ],
    'int' => \PHP_INT_MAX,
    'float' => \PHP_FLOAT_MAX,
    'null' => null,
    'array' => ['foo', 'bar'],
    'foo' => 'bar',
    'foo.bar' => 'foo-bar',
    'foobar' => [
        'bar.baz' => 'foo-bar-baz',
    ],
    'bar' => 'baz',
    'baz' => 'bat',
    'associate' => [
        'x' => 'xxx',
        'y' => 'yyy',
    ],
    'x' => [
        'z' => 'zoo',
    ],
];

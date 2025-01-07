<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

$workingDirectory = \getcwd() ?: __DIR__;

require_once $workingDirectory . '/vendor/autoload.php';

return ECSConfig::configure()->withSets(
    \array_filter(
        [
            $workingDirectory . '/vendor/ghostwriter/coding-standard/config/ecs.php',
            $workingDirectory . '/config/ecs.php',
        ],
        'file_exists'
    )
);

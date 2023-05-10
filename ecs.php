<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $directory = getcwd();
    $ecsConfig->import($directory . '/vendor/ghostwriter/coding-standard/ecs.php');
    $ecsConfig->paths([
        $directory . '/ecs.php',
        $directory . '/README.md',
        $directory . '/rector.php',
        $directory . '/src',
        $directory . '/tests',
    ]);
    $ecsConfig->skip([$directory . '/tests/Fixture/*', $directory . '/vendor/*']);
};

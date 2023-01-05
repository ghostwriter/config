<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;

use PhpCsFixer\Fixer\Import\GroupImportFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\GeneralPhpdocAnnotationRemoveFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
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
    $ecsConfig->skip([
        $directory . '/tests/Fixture/*',
        $directory . '/vendor/*',
        GroupImportFixer::class,
        BinaryOperatorSpacesFixer::class,
        GeneralPhpdocAnnotationRemoveFixer::class,
        PhpdocLineSpanFixer::class,
        PhpdocTrimFixer::class,
    ]);
};

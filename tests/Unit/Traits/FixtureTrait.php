<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit\Traits;

use Ghostwriter\Config\Contract\Exception\ConfigExceptionInterface;
use InvalidArgumentException;

trait FixtureTrait
{
    protected static function fixture(string $path): string
    {
        $realpath = realpath(sprintf('%s/Fixture/config.%s.php', dirname(__DIR__, 2), mb_strtolower($path)));

        if ($realpath === false) {
            throw new class(
                'Invalid fixture path: ' . $path
            ) extends InvalidArgumentException implements ConfigExceptionInterface {
            };
        }

        return $realpath;
    }
}

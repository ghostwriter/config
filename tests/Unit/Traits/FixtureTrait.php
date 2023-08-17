<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Tests\Unit\Traits;

use Ghostwriter\Config\Exception\ConfigFileNotFoundException;

trait FixtureTrait
{
    protected static function fixture(string $path): string
    {
        $realpath = realpath(sprintf('%s/Fixture/config.%s.php', dirname(__DIR__, 2), mb_strtolower($path)));

        if ($realpath === false) {
            throw new ConfigFileNotFoundException($path);
        }

        return $realpath;
    }
}

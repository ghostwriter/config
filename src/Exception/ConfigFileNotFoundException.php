<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\ExceptionInterface;
use InvalidArgumentException;

final class ConfigFileNotFoundException extends InvalidArgumentException implements ExceptionInterface
{
}

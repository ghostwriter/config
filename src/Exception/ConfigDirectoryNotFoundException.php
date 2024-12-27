<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ExceptionInterface;
use InvalidArgumentException;

final class ConfigDirectoryNotFoundException extends InvalidArgumentException implements ExceptionInterface {}

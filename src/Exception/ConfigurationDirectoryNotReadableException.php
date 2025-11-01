<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use InvalidArgumentException;

final class ConfigurationDirectoryNotReadableException extends InvalidArgumentException implements ConfigurationExceptionInterface {}

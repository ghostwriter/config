<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use InvalidArgumentException;

final class ConfigurationKeyMustBeStringException extends InvalidArgumentException implements ConfigurationExceptionInterface {}

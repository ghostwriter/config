<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ConfigExceptionInterface;
use InvalidArgumentException;

final class ConfigFileNotFoundException extends InvalidArgumentException implements ConfigExceptionInterface {}

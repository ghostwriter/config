<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ConfigurationExceptionInterface;
use RuntimeException;

final class FailedToLoadConfigurationFileException extends RuntimeException implements ConfigurationExceptionInterface {}

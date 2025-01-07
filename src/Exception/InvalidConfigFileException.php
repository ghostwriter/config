<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use InvalidArgumentException;

final class InvalidConfigFileException extends InvalidArgumentException implements ExceptionInterface {}

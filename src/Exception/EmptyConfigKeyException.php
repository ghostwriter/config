<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use InvalidArgumentException;

final class EmptyConfigKeyException extends InvalidArgumentException implements ExceptionInterface {}

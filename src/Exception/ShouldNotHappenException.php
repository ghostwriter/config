<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use Ghostwriter\Config\Interface\ExceptionInterface;
use LogicException;

final class ShouldNotHappenException extends LogicException implements ExceptionInterface {}

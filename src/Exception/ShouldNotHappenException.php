<?php

declare(strict_types=1);

namespace Ghostwriter\Config\Exception;

use LogicException;

final class ShouldNotHappenException extends LogicException implements ExceptionInterface {}

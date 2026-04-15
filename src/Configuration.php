<?php

declare(strict_types=1);

namespace Ghostwriter\Config;

use Ghostwriter\Config\Interface\ConfigurationInterface;

/**
 * @template T of (array<non-empty-string,T>|bool|float|int|null|string)
 */
final class Configuration extends AbstractConfiguration implements ConfigurationInterface {}

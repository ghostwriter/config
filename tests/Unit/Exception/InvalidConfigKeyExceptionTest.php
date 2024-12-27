<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\InvalidConfigKeyException;
use Ghostwriter\Config\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

#[CoversClass(InvalidConfigKeyException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class InvalidConfigKeyExceptionTest extends AbstractTestCase
{
    public function testCreateDotKey(): void
    {
        $key = '...';

        $this->expectException(ExceptionInterface::class);

        $this->expectException(InvalidConfigKeyException::class);

        $this->expectExceptionMessage($key);

        $this->config([
            $key => 'value',
        ]);
    }

    public function testCreateIntKey(): void
    {
        $this->expectException(ExceptionInterface::class);

        $this->expectException(InvalidConfigKeyException::class);

        $this->config([
            1 => 'value',
        ]);
    }

    public function testSetDotEmpty(): void
    {
        $config = $this->config();

        $key = '..invalid..';

        $this->expectException(ExceptionInterface::class);

        $this->expectException(InvalidConfigKeyException::class);

        $this->expectExceptionMessage($key);

        $config->set($key, 'value');
    }
}

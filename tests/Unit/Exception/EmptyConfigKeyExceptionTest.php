<?php

declare(strict_types=1);

namespace Tests\Unit\Exception;

use Ghostwriter\Config\Config;
use Ghostwriter\Config\ConfigFactory;
use Ghostwriter\Config\Exception\EmptyConfigKeyException;
use Ghostwriter\Config\Interface\ExceptionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Tests\Unit\AbstractTestCase;

#[CoversClass(EmptyConfigKeyException::class)]
#[CoversClass(ConfigFactory::class)]
#[CoversClass(Config::class)]
final class EmptyConfigKeyExceptionTest extends AbstractTestCase
{
    public function testCreateEmptyKey(): void
    {
        $this->expectException(ExceptionInterface::class);

        $this->expectException(EmptyConfigKeyException::class);

        $this->config([
            '' => 'value',
        ]);
    }

    public function testSetEmptyKey(): void
    {
        $config = $this->config();

        $this->expectException(ExceptionInterface::class);

        $this->expectException(EmptyConfigKeyException::class);

        $config->set('', 'value');
    }
}

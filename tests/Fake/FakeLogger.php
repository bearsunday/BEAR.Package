<?php

declare(strict_types=1);

namespace BEAR\Package;

use Psr\Log\AbstractLogger;
use Stringable;

class FakeLogger extends AbstractLogger
{
    public string $called = '';

    public function emergency($message, array $context = []): void
    {
    }

    public function critical($message, array $context = []): void
    {
    }

    public function error($message, array $context = []): void
    {
        $this->called = __FUNCTION__;
    }

    public function warning($message, array $context = []): void
    {
    }

    public function debug($message, array $context = []): void
    {
        $this->called = __FUNCTION__;
    }

    public function log($level, $message, array $context = []): void
    {
    }
};

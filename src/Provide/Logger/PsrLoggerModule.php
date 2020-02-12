<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use Psr\Log\LoggerInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class PsrLoggerModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(LoggerInterface::class)->toProvider(MonologProvider::class)->in(Scope::SINGLETON);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Logger;

use Override;
use Psr\Log\LoggerInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

final class PsrLoggerModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(LoggerInterface::class)->toProvider(MonologProvider::class)->in(Scope::SINGLETON);
    }
}

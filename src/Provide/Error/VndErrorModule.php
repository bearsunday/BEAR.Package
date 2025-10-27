<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Error\ErrorInterface;
use Override;
use Ray\Di\AbstractModule;

final class VndErrorModule extends AbstractModule
{
    #[Override]
    protected function configure(): void
    {
        $this->bind(ErrorLogger::class);
        $this->bind(ErrorInterface::class)->to(ErrorHandler::class);
        $this->bind(ErrorPageFactoryInterface::class)->to(DevVndErrorPageFactory::class);
    }
}

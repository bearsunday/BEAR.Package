<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Error\ErrorInterface;
use Ray\Di\AbstractModule;

class VndErrorModule extends AbstractModule
{
    protected function configure() : void
    {
        $this->bind(ErrorLogger::class);
        $this->bind(ErrorInterface::class)->to(ErrorHandler::class);
        $this->bind(ErrorPageFactoryInterface::class)->to(DevVndErrorPageFactory::class);
    }
}

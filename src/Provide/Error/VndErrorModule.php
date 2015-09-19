<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Error;

use BEAR\Sunday\Extension\Error\ErrorInterface;
use Ray\Di\AbstractModule;

class VndErrorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ErrorInterface::class)->to(VndErrorHandler::class);
    }
}

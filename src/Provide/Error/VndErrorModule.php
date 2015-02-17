<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Package\Provide\Error\VndError;
use BEAR\Sunday\Extension\Error\ErrorInterface;
use Ray\Di\AbstractModule;

class VndErrorModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(ErrorInterface::class)->to(VndError::class);
    }
}

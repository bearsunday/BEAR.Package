<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;

class WebRouterModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind(RouterInterface::class)->to(WebRouter::class);
    }
}

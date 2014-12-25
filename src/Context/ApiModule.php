<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Context;

use BEAR\Package\Provide\Router\ApiRouter;
use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;

class ApiModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RouterInterface::class)->to(ApiRouter::class);
        $this->bind(RouterInterface::class)->annotatedWith('original')->to(ApiRouter::class);
        $this->bind()->annotatedWith('default_route_host')->toInstance('app://self');
    }
}

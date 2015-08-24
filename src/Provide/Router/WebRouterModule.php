<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RouterInterface::class)->to(WebRouter::class)->in(Scope::SINGLETON);
        $this->bind(WebRouterInterface::class)->to(WebRouter::class)->in(Scope::SINGLETON);
        $this->bind(HttpMethodParamsInterface::class)->to(HttpMethodParams::class)->in(Scope::SINGLETON);
    }
}

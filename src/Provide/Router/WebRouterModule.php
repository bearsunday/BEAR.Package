<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;

class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(RouterInterface::class)->to(WebRouter::class);
        $this->bind(WebRouterInterface::class)->to(WebRouter::class);
        $this->bind(HttpMethodParamsInterface::class)->to(HttpMethodParams::class);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Override;
use Ray\Di\AbstractModule;

final class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(RouterInterface::class)->to(WebRouter::class);
        $this->bind(WebRouterInterface::class)->to(WebRouter::class);
        $this->bind(HttpMethodParamsInterface::class)->to(HttpMethodParams::class);
    }
}

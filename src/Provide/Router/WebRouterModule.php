<?php declare(strict_types=1);
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Router;

use BEAR\Sunday\Extension\Router\RouterInterface;
use Ray\Di\AbstractModule;

class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RouterInterface::class)->to(WebRouter::class);
        $this->bind(WebRouterInterface::class)->to(WebRouter::class);
        $this->bind(HttpMethodParamsInterface::class)->to(HttpMethodParams::class);
    }
}

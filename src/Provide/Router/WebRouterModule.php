<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(WebRouterInterface::class)->to(WebRouter::class)->in(Scope::SINGLETON);
        $this->bind(HttpMethodParamsInterface::class)->to(HttpMethodParams::class)->in(Scope::SINGLETON);
    }
}

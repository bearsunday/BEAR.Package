<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Ray\Di\AbstractModule;

class WebRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Extension\Router\RouterInterface')->to('BEAR\Package\Provide\Router\Router');
        $this->bind('BEAR\Package\Provide\Router\Adapter\AdapterInterface')->to('BEAR\Package\Provide\Router\Adapter\WebRouter');
    }
}

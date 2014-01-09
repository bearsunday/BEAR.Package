<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Ray\Di\AbstractModule;

class AuraRouterModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Extension\Router\RouterInterface')->to(__NAMESPACE__ . '\AuraRouter');
    }
}

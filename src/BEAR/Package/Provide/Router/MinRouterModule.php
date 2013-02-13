<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\Router;

use Ray\Di\AbstractModule;

/**
 * Router module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class MinRouterModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\Router\RouterInterface')
            ->to(__NAMESPACE__ . '\MinRouter');
    }
}

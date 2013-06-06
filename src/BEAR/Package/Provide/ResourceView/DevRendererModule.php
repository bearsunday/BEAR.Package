<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

/**
 * Resource renderer module - DEV
 */
class DevRendererModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\Resource\RenderInterface')->to(__NAMESPACE__ . '\DevTemplateEngineRenderer')->in(Scope::SINGLETON);
    }
}

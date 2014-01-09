<?php

namespace Demo\Sandbox\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;

/**
 * Application Dependency
 */
class Dependency extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // application bindings
        $this->bind()->annotatedWith('greeting_msg')->toInstance('Hola');
        $this->bind('BEAR\Resource\RenderInterface')->annotatedWith('hal')->to('BEAR\Package\Provide\ResourceView\HalRenderer')->in(Scope::SINGLETON);
    }
}

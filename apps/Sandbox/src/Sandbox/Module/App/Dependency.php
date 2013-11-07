<?php

namespace Sandbox\Module\App;

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

        $path = dirname(dirname(dirname(dirname(__DIR__)))) . '/var/.htpasswd';
        $this->bind()->annotatedWith('basic_pass_file')->toInstance($path);
        $this->bind('Sandbox\Interceptor\BasicAuth\CertificateAuthorityInterface')->to('Sandbox\Interceptor\BasicAuth\FileUserList');
    }
}

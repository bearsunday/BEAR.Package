<?php

namespace Demo\Sandbox\Module\App\Aspect;

use BEAR\Package\Module as PackageModule;
use BEAR\Sunday\Module as SundayModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

class DevModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // smarty
        $smarty = $this->requestInjection('Smarty');
        /** @var $smarty \Smarty */
        $smarty->force_compile = true;

        // bind tmp writable checker
        $checker = $this->requestInjection('Demo\Sandbox\Interceptor\Checker');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Index'),
            $this->matcher->startWith('on'),
            [$checker]
        );
    }
}

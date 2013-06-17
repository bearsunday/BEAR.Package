<?php

namespace Sandbox\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;
use Sandbox\Interceptor\TimeMessage;
use Ray\Di\Module\InjectorModule;
use BEAR\Package\Module\Resource\ResourceGraphModule;
/**
 * Application module
 */
class AopModule extends AbstractModule
{
    /**
     * @var array
     */
    private $params;

    protected function configure()
    {
        $this->install(new Package\Module\Database\Dbal\DbalModule($this));
        $this->install(new InjectorModule($this));
        $this->install(new ResourceGraphModule($this));
        // install package aop

        // Injector module ('Injected injector' knows all bindings)

        // install application aspect
        $this->installTimeMessage();
        $this->installNewBlogPost();
        $this->installAuraContactForm();
    }

    /**
     * Add time message aspect
     */
    private function installTimeMessage()
    {
        // time message binding
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\App\First\Greeting\Aop'),
            $this->matcher->any(),
            [new TimeMessage]
        );
    }

    /**
     * @Form - Plain form
     */
    private function installNewBlogPost()
    {
        $blogPost = $this->requestInjection('Sandbox\Interceptor\Form\BlogPost');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Blog\Posts\Newpost'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [$blogPost]
        );
    }

    /**
     * @Form - Aura.Input form
     */
    private function installAuraContactForm()
    {
        $auraContact = $this->requestInjection('Sandbox\Interceptor\Form\AuraContact');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Demo\Form\Auraform'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [$auraContact]
        );
    }
}

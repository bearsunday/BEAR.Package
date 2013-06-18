<?php

namespace Sandbox\Module;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\Scope;
use Sandbox\Interceptor\TimeMessage;
use BEAR\Package\Module\Database\Dbal\DbalModule;
use BEAR\Package\Module\Resource\ResourceGraphModule;
use BEAR\Sunday\Module\Cqrs\CacheModule;
use Ray\Di\Module\InjectorModule;
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

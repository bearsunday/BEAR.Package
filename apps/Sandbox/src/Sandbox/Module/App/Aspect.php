<?php

namespace Sandbox\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Sandbox\Interceptor\TimeMessage;

/**
 * Application Aspect
 */
class Aspect extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // install application aspect
        $this->installTimeMessage();
        $this->installNewBlogPost();
        $this->installAuraContactForm();
        $this->installAuth();
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

    /**
     * add authentication aspect
     */
    private function installAuth()
    {
        $basicAuth = $this->requestInjection('Sandbox\Interceptor\BasicAuthInterceptor');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('BEAR\Resource\ResourceObject'),
            $this->matcher->annotatedWith('Sandbox\Annotation\Auth'),
            [$basicAuth]
        );
    }
}

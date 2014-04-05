<?php

namespace Demo\Sandbox\Module\App;

use BEAR\Package;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

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
    }

    /**
     * Add time message aspect
     */
    private function installTimeMessage()
    {
        // time message binding
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Demo\Sandbox\Resource\App\First\Greeting\Aop'),
            $this->matcher->any(),
            [$this->requestInjection('Demo\Sandbox\Interceptor\TimeMessage')]
        );
    }

    /**
     * @Form - Plain form
     */
    private function installNewBlogPost()
    {
        $this->bindInterceptor(
            $this->matcher->logicalOr(
                $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Blog\Posts\Newpost'),
                $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Blog\Posts\Edit')
            ),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [$this->requestInjection('Demo\Sandbox\Interceptor\Form\BlogPost')]
        );
    }

    /**
     * @Form - Aura.Input form
     */
    private function installAuraContactForm()
    {
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Demo\Sandbox\Resource\Page\Demo\Form\Auraform'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [$auraContact = $this->requestInjection('Demo\Sandbox\Interceptor\Form\AuraContact')]
        );
    }
}

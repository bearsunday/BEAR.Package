<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module\Common;

use BEAR\Sunday\Module as SundayModule;
use BEAR\Package\Module as PackageModule;
use Sandbox\Interceptor\PostFormValidator;
use Sandbox\Interceptor\TimeMessage;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;
/**
 * Application module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class AppModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        // di - application
        $this->bind()->annotatedWith('greeting_msg')->toInstance('Hola');
        $this->bind('BEAR\Sunday\Application\Context')->to('Sandbox\App');
        // di - Package
        $this->install(new PackageModule\Package\PackageModule($this));
        // di - Sunday
        $this->install(new SundayModule\Resource\ApcModule);
        $this->install(new SundayModule\Resource\HalModule);
        $this->install(new SundayModule\WebContext\AuraWebModule);
        $this->install(new SundayModule\SchemeModule(__NAMESPACE__ . '\SchemeCollectionProvider'));
        $this->install(new SundayModule\Cqrs\CacheModule($this));
        $this->bindAppResourceHalRender();
        // aop
        $this->installTimeMessage();
        $this->installWritableChecker();
        $this->installNewpostFormValidator();
    }

    /**
     * Check writable directory
     */
    private function installWritableChecker()
    {
        // bind tmp writable checker
        $checker = $this->requestInjection('\Sandbox\Interceptor\Checker');
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Index'),
            $this->matcher->startWith('on'),
            [$checker]
        );
    }

    /**
     * @Form - bind form validator
     */
    private function installNewpostFormValidator()
    {
        $this->bindInterceptor(
            $this->matcher->subclassesOf('Sandbox\Resource\Page\Blog\Posts\Newpost'),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Form'),
            [new PostFormValidator]
        );
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
     * for RESTbucks hal print
     */
    private function bindAppResourceHalRender()
    {
        $this->bind('BEAR\Resource\Renderable')
             ->annotatedWith('hal')
             ->to('BEAR\Sunday\Resource\View\HalRenderer')
             ->in(Scope::SINGLETON);
    }
}

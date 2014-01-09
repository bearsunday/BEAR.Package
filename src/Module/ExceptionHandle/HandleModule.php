<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\ExceptionHandle;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

/**
 * Exception handle module
 */
class HandleModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->bind('')
            ->annotatedWith('exceptionTpl')
            ->toInstance(__DIR__ . '/template/view.php');
        $this
            ->bind('BEAR\Resource\ResourceObject')
            ->annotatedWith('errorPage')
            ->to('BEAR\Package\Dev\Debug\ExceptionHandle\ErrorPage');
        $this
            ->bind('BEAR\Package\Dev\Debug\ExceptionHandle\ExceptionHandlerInterface')
            ->to('BEAR\Package\Dev\Debug\ExceptionHandle\ExceptionHandler')
            ->in(Scope::SINGLETON);
    }
}

<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\ExceptionHandle;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class HandleModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('')
            ->annotatedWith('exceptionTpl')
            ->toInstance(__DIR__ . '/template/exception.php');
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

<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\ExceptionHandle;

use Ray\Di\AbstractModule;

/**
 * Exception handle module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
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
            ->bind('BEAR\Resource\AbstractObject')
            ->annotatedWith('errorPage')
            ->to('BEAR\Package\Debug\ExceptionHandle\ErrorPage');
        $this
            ->bind('BEAR\Package\Debug\ExceptionHandle\ExceptionHandlerInterface')
            ->to('BEAR\Package\Debug\ExceptionHandle\ExceptionHandler');
    }
}

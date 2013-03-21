<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Aop;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

/**
 * Exception handle module
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class NamedArgsModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->bind('Ray\Aop\NamedArgsInterface')
            ->to('Ray\Aop\NamedArgs');
    }
}

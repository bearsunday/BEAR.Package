<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Aop;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class NamedArgsModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('Ray\Aop\NamedArgsInterface')
            ->to('Ray\Aop\NamedArgs');
    }
}

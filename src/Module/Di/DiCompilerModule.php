<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Di;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class DiCompilerModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\InstanceInterface')->toProvider(__NAMESPACE__ . '\DiCompilerProvider')->in(Scope::SINGLETON);
    }
}

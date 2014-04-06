<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Di;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class DiModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind('Ray\Di\InjectorInterface')->toProvider(__NAMESPACE__ . '\DiProvider')->in(Scope::SINGLETON);
    }
}

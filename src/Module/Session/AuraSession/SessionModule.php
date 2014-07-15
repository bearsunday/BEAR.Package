<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Session\AuraSession;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SessionModule extends AbstractModule
{
    public function configure()
    {
        $this->bind('Aura\Session\Manager')
            ->toProvider('BEAR\Package\Module\Session\AuraSession\SessionProvider')
            ->in(Scope::SINGLETON);
    }
}

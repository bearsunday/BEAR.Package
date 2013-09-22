<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Log;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

/**
 * Zf2 log module
 */
class ZfLogModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Guzzle\Log\LogAdapterInterface')->toProvider('BEAR\Package\Module\Log\ZfLogModule\ZfLogProvider')->in(Scope::SINGLETON);
    }
}

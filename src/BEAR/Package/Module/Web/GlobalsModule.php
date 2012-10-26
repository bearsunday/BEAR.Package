<?php
/**
 * This file is part of the BEAR.Packages package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Web;

use Ray\Di\AbstractModule;


/**
 * GLOBAL module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class GlobalsModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Web\GlobalsInterface')->to('BEAR\Package\Web\Globals');
    }
}

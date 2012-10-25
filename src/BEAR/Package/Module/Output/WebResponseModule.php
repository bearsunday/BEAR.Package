<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Output;

use Ray\Di\AbstractModule;

/**
 * Web response module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class WebResponseModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Web\ResponseInterface')->to('BEAR\Package\Web\SymfonyResponse');
    }
}

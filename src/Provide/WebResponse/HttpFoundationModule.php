<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use Ray\Di\AbstractModule;

/**
 * Web response module
 */
class HttpFoundationModule extends AbstractModule
{
    /**
     * Configure
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Extension\WebResponse\ResponseInterface')->to(__NAMESPACE__ . '\HttpFoundation');
    }
}

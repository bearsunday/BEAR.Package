<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\WebResponse;

use Ray\Di\AbstractModule;

class HttpFoundationModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Extension\WebResponse\ResponseInterface')->to(__NAMESPACE__ . '\HttpFoundation');
    }
}

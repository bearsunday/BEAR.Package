<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use Ray\Di\AbstractModule;

/**
 * Application logger module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class ApplicationLoggerModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface')
            ->to(__NAMESPACE__ . '\ApplicationLogger');
    }
}

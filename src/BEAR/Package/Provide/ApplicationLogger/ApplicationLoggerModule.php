<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

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
        // log register
        $this
            ->bind('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface')
            ->to(__NAMESPACE__ . '\ApplicationLogger');

        // log writer
        $this
            ->bind('BEAR\Resource\LogWriterInterface')
            ->toProvider(__NAMESPACE__ . '\ResourceLog\WritersProvider')
            ->in(Scope::SINGLETON);

        $this
            ->bind('Ray\Di\LoggerInterface')
            ->to('BEAR\Package\Provide\Application\DiLogger')
            ->in(Scope::SINGLETON);

    }
}

<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class ApplicationLoggerModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // log register
        $this
            ->bind('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface')
            ->to(__NAMESPACE__ . '\ApplicationLogger')
            ->in(Scope::SINGLETON);

        // log writer
        $this
            ->bind('BEAR\Resource\LogWriterInterface')
            ->toProvider(__NAMESPACE__ . '\ResourceLog\DevWritersProvider')
            ->in(Scope::SINGLETON);

        $this
            ->bind('Ray\Di\LoggerInterface')
            ->to('BEAR\Package\Provide\Application\DiLogger')
            ->in(Scope::SINGLETON);
    }
}

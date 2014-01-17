<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ApplicationLogger;

use Ray\Di\AbstractModule;
use Ray\Di\Di\Scope;

class DevApplicationLoggerModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // log register
        $this
            ->bind('BEAR\Sunday\Extension\ApplicationLogger\ApplicationLoggerInterface')
            ->to(__NAMESPACE__ . '\DevApplicationLogger');

        // log writer
        $this
            ->bind('BEAR\Resource\LogWriterInterface')
            ->toProvider(__NAMESPACE__ . '\ResourceLog\DevWritersProvider')
            ->in(Scope::SINGLETON);
    }
}

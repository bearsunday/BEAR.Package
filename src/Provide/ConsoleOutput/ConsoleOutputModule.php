<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ConsoleOutput;

use Ray\Di\AbstractModule;

class ConsoleOutputModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Sunday\Extension\ConsoleOutput\ConsoleOutputInterface')->to(__NAMESPACE__ . '\ConsoleOutput');
    }
}

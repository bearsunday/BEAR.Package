<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\PHPTAL;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * PHPTAL module
 *
 * @package    BEAR.Package
 * @subpackage Module
 */
class PHPTALModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface')
            ->to(__NAMESPACE__ . '\PHPTALAdapter')
            ->in(Scope::SINGLETON);
        $this
            ->bind('PHPTAL')
            ->toProvider(__NAMESPACE__ . '\PHPTALProvider')
            ->in(Scope::SINGLETON);
    }
}

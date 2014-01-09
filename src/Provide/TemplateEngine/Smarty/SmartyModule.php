<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Smarty;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class SmartyModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface')
            ->to(__NAMESPACE__ . '\SmartyAdapter')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Smarty')
            ->toProvider(__NAMESPACE__ . '\SmartyProvider')
            ->in(Scope::SINGLETON);
    }
}

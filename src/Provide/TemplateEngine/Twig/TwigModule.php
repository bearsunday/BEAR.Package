<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\Twig;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Smarty module
 */
class TwigModule extends AbstractModule
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
            ->to(__NAMESPACE__ . '\TwigAdapter')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Twig_Environment')
            ->toProvider(__NAMESPACE__ . '\TwigProvider')
            ->in(Scope::SINGLETON);
    }
}

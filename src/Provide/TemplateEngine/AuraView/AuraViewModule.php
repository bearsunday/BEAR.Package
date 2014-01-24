<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\TemplateEngine\AuraView;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class AuraViewModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Sunday\Extension\TemplateEngine\TemplateEngineAdapterInterface')
            ->to(__NAMESPACE__ . '\AuraViewAdapter')
            ->in(Scope::SINGLETON);
        $this
            ->bind('Aura\View\AbstractTemplate')
            ->toProvider(__NAMESPACE__ . '\AuraViewProvider')
            ->in(Scope::SINGLETON);
    }
}

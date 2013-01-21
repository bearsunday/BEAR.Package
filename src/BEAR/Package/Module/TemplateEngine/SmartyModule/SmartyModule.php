<?php
/**
 * This file is part of the BEAR.Sunday package
 *
 * @package BEAR.Sunday
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\TemplateEngine\SmartyModule;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Smarty module
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class SmartyModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this->bind('Smarty')->toProvider('BEAR\Package\Module\TemplateEngine\SmartyModule\SmartyProvider')->in(
            Scope::SINGLETON
        );
        $this->bind('BEAR\Sunday\Resource\View\TemplateEngineAdapterInterface')->to(
            'BEAR\Package\Module\TemplateEngine\SmartyModule\SmartyAdapter'
        )->in(Scope::SINGLETON);
    }
}

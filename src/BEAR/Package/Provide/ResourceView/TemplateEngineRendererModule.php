<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;

/**
 * Resource renderer module - PROD
 *
 * @package    BEAR.Sunday
 * @subpackage Module
 */
class TemplateEngineRendererModule extends AbstractModule
{
    /**
     * Configure dependency binding
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Resource\RenderInterface')
            ->to(__NAMESPACE__ . '\TemplateEngineRenderer');
    }
}

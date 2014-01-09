<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Hal(Hypertext Application Language) render module
 */
class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind('BEAR\Resource\RenderInterface')->to(__NAMESPACE__ . '\HalRenderer')->in(Scope::SINGLETON);
    }
}

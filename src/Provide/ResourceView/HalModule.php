<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Provide\ResourceView;

use Ray\Di\AbstractModule;
use Ray\Di\Scope;
use Ray\Di\Di;

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
        $this->bind('BEAR\Package\Provide\ResourceView\HalFactoryInterface')->to(__NAMESPACE__ . '\HalFactory');
        $this->bind('BEAR\Package\Provide\ResourceView\UriMapperInterface')->to(__NAMESPACE__ . '\SchemeUriMapper');
    }
}

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
 * Json render module
 */
class JsonModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->bind('BEAR\Resource\RenderInterface')
            ->to('BEAR\Package\Provide\ResourceView\JsonRenderer')
            ->in(Scope::SINGLETON);
    }
}

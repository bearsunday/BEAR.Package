<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Cache;

use Ray\Di\AbstractModule;

class CacheAspectModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        // bind @Cache annotated method in any class
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\Cache'),
            [__NAMESPACE__ . '\Interceptor\CacheLoader']
        );
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith('BEAR\Sunday\Annotation\CacheUpdate'),
            [__NAMESPACE__ . '\Interceptor\CacheUpdater']
        );
    }
}

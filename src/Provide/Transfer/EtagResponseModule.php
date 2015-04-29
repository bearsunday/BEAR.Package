<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Transfer;

use BEAR\Package\Annotation\Etag;
use Ray\Di\AbstractModule;

class EtagResponseModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bindInterceptor(
            $this->matcher->annotatedWith(Etag::class),
            $this->matcher->startsWith('transfer'),
            [EtagResponseInterceptor::class]
        );
    }
}

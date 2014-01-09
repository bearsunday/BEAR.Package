<?php

namespace Demo\Helloworld;

use BEAR\Package\Provide\Application\AbstractApp;
use BEAR\Resource\ResourceInterface;
use Ray\Di\Di\Inject;

final class App extends AbstractApp
{
    /**
     * @param \BEAR\Resource\ResourceInterface $resource
     *
     * @Inject
     */
    public function __construct(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }
}

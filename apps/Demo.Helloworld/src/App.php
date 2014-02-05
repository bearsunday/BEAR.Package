<?php

namespace Demo\Helloworld;

use BEAR\Resource\ResourceInterface;
use Ray\Di\Di\Inject;

final class App
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    public $resource;

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

<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Annotation\ReturnCreatedResource;
use Ray\Di\AbstractModule;

class CreatedResourceModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(CreatedResourceRenderer::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(ReturnCreatedResource::class),
            [CreatedResourceInterceptor::class]
        );
    }
}

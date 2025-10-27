<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Annotation\ReturnCreatedResource;
use Override;
use Ray\Di\AbstractModule;

final class CreatedResourceModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(CreatedResourceRenderer::class);
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(ReturnCreatedResource::class),
            [CreatedResourceInterceptor::class],
        );
    }
}

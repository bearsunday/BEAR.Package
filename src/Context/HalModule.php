<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Provide\Representation\CreatedResourceRenderer;
use BEAR\Package\Provide\Representation\RouterReverseLink;
use BEAR\Package\Provide\Representation\RouterReverseLinker;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ReverseLinkerInterface;
use BEAR\Resource\ReverseLinkInterface;
use Override;
use Ray\Di\AbstractModule;

final class HalModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(CreatedResourceRenderer::class);
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
        $this->bind(ReverseLinkerInterface::class)->to(RouterReverseLinker::class);
        /** @psalm-suppress DeprecatedClass */
        $this->bind(ReverseLinkInterface::class)->to(RouterReverseLink::class);
    }
}

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
use Ray\Di\AbstractModule;

class HalModule extends AbstractModule
{
    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(CreatedResourceRenderer::class);
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
        $this->bind(ReverseLinkerInterface::class)->to(RouterReverseLinker::class);
        $this->bind(ReverseLinkInterface::class)->to(RouterReverseLink::class);
    }
}

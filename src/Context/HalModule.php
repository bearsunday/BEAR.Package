<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Provide\Representation\CreatedResourceRenderer;
use BEAR\Package\Provide\Representation\RouterReverseLink;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\ReverseLinkInterface;
use Ray\Di\AbstractModule;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure() : void
    {
        $this->bind(CreatedResourceRenderer::class);
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
        $this->bind(ReverseLinkInterface::class)->to(RouterReverseLink::class);
    }
}

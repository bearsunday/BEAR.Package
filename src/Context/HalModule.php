<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Package\Provide\Representation\CreatedResourceRenderer;
use BEAR\Package\Provide\Representation\HalLink;
use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Resource\RenderInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(HalLink::class);
        $this->bind(CreatedResourceRenderer::class);
        $this->bind(RenderInterface::class)->to(HalRenderer::class)->in(Scope::SINGLETON);
    }
}

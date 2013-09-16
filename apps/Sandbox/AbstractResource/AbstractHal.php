<?php

namespace Sandbox\Resource\AbstractResource;

use BEAR\Resource\ResourceObject;
use BEAR\Resource\RenderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Resource objects using Hal renderer
 */
abstract class AbstractHal extends ResourceObject
{
    /**
     * Set HalRenderer
     *
     * @param RenderInterface $renderer
     *
     * @Inject
     * @Named("hal")
     */
    public function setRenderer(RenderInterface $renderer)
    {
        $this->renderer = $renderer;
    }
}

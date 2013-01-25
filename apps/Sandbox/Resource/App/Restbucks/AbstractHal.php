<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\Restbucks;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\RenderInterface;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;

/**
 * Resource objects using Hal renderer
 *
 * @package    Sandbox
 * @subpackage Resource
 */
abstract class AbstractHal extends AbstractObject
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

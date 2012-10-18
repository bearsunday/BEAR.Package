<?php
/**
 * @package    Sandbox
 * @subpackage Resource
 */
namespace Sandbox\Resource\App\Restbucks;

use BEAR\Resource\AbstractObject;
use BEAR\Resource\Renderable;
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
     * @param Renderable $renderer
     *
     * @Inject
     * @Named("hal")
     */
    public function setRenderer(Renderable $renderer)
    {
        $this->renderer = $renderer;
    }
}

<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package;

use BEAR\Package\Provide\Transfer\HalResponder;
use Ray\Di\AbstractModule;
use BEAR\Resource\RenderInterface;
use BEAR\Resource\TransferInterface;
use BEAR\Package\Provide\Representation\HalRenderer;

class HalModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(RenderInterface::class)->to(HalRenderer::class);
        $this->bind(TransferInterface::class)->to(HalResponder::class);
    }
}

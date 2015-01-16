<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Context;

use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Package\Provide\Transfer\HalResponder;
use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Extension\Transfer\TransferInterface;
use Ray\Di\AbstractModule;

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

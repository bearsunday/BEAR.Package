<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Resource\HalRenderer;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\RenderInterface;
use BEAR\Sunday\Module\Annotation\DoctrineAnnotationModule;
use BEAR\Sunday\Provide\Router\RouterModule;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

class HalModuleTest extends TestCase
{
    public function testModule()
    {
        $renderer = (new Injector(new HalModule(new RouterModule(new DoctrineAnnotationModule(new ResourceModule('FakeVendor\HelloWorld'))))))->getInstance(RenderInterface::class);
        $this->assertInstanceOf(HalRenderer::class, $renderer);
    }
}

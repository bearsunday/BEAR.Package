<?php

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Representation\NoContentRenderer;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\HelloWorld\Resource\App\User;
use Ray\Di\Injector;

class NoContentRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @ covers BEAR\Package\Provide\Representation\NoContentRenderer::render()
     */
    public function testRender()
    {
        $user = new User;
        $user->setRenderer(new NoContentRenderer);
        $result = (string) $user;
        $expect = '';
        $this->assertSame($expect, $result);
    }
}

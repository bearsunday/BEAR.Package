<?php

namespace BEAR\Package;

use Aura\Router\Router;
use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Package\Provide\Router\AuraRouter;
use BEAR\Package\Provide\Router\AuraRouterProvider;
use BEAR\Resource\ResourceClientFactory;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationReader;

class HalRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    /**
     * @var HalRenderer
     */
    private $hal;

    public function setUp()
    {
        $router = (new AuraRouterProvider(new AppMeta('FakeVendor\HelloWorld')))->get();
        $this->hal = new HalRenderer(new AnnotationReader, $router);
        $this->resource = (new ResourceClientFactory)->newClient($_ENV['TMP_DIR'], 'FakeVendor\HelloWorld');
    }

    public function testRender()
    {
        $ro = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "org_id": "o1",
    "_links": {
        "self": {
            "href": "/user/1"
        },
        "friend": {
            "href": "/friend/f1"
        },
        "org": {
            "href": "/org?id=o1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testRenderPost()
    {
        $ro = $this->resource->post->uri('app://self/user')->withQuery(['id' => 1])->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "_links": {
        "self": {
            "href": "/user/1"
        },
        "friend": {
            "href": "/friend/f1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }
}

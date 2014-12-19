<?php

namespace BEAR\Package;

use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Resource\ResourceClientFactory;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Uri;
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
        $this->hal = new HalRenderer(new AnnotationReader);
        $this->resource = (new ResourceClientFactory)->newInstance('FakeVendor\HelloWorld', new AnnotationReader);
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
            "href": "/user?id=1"
        },
        "friend": {
            "href": "/friend?id=f1"
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
            "href": "/user?id=1"
        },
        "friend": {
            "href": "/friend?id=f1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }
}

<?php

namespace BEAR\Package;

use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\HelloWorld\Resource\App\FakeUser;

class HalRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HalRenderer
     */
    private $hal;

    public function setUp()
    {
        $this->hal = new HalRenderer(new AnnotationReader);
    }

    public function testRender()
    {
        $ro = new FakeUser;
        $ro->uri = new Uri('app://self/user');
        $ro->onGet(1);
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "org_id": "o1",
    "_links": {
        "self": {
            "href": "/user"
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
}

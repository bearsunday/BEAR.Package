<?php

namespace BEAR\Package;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Package\Provide\Router\AuraRouterProvider;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Di\Injector;

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
        $router = (new AuraRouterProvider(new AppMeta('FakeVendor\HelloWorld'), 'page://self'))->get();
        $this->hal = new HalRenderer(new AnnotationReader, $router);
        $this->resource = (new Injector(new ResourceModule('FakeVendor\HelloWorld')))->getInstance(ResourceInterface::class);
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

    public function testRenderEmbed()
    {
        $ro = $this->resource->get->uri('page://self/emb')->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "_embedded": {
        "user": {
            "id": "1",
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
    },
    "_links": {
        "self": {
            "href": "/emb"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testRenderScalar()
    {
        $ro = $this->resource->get->uri('app://self/scalar')->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "value": "ak",
    "_links": {
        "self": {
            "href": "/scalar"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testOptions()
    {
        $ro = $this->resource->options->uri('app://self/scalar')->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '';
        $this->assertSame($expect, $result);
    }
}

<?php

namespace BEAR\Package;

use Aura\Router\RouterFactory;
use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Package\Provide\Router\AuraRouter;
use BEAR\Package\Provide\Router\AuraRouterProvider;
use BEAR\Package\Provide\Router\HttpMethodParams;
use BEAR\Package\Provide\Router\WebRouter;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\HelloWorld\Resource\App\Task;
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
        $router = new WebRouter('page://self', new HttpMethodParams());
        $this->hal = new HalRenderer(new AnnotationReader, $router);
        $this->resource = (new Injector(new ResourceModule('FakeVendor\HelloWorld')))->getInstance(ResourceInterface::class);
    }

    public function testRender()
    {
        $ro = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1, 'type' => 'type_a'])->eager->request();
        $ro->setRenderer($this->hal);
        $result = (string) $ro;
        $expect = '{
    "id": 1,
    "friend_id": "f1",
    "org_id": "o1",
    "_links": {
        "self": {
            "href": "/user?id=1&type=type_a"
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

    public function testHalRendererNoParam()
    {
        $halRenderer = new HalRenderer(new AnnotationReader, new FakeRouter);
        $ro = new Task;
        $ro->uri = new Uri('app://self/task');
        $ro->uri->method = 'get';
        $hal = $halRenderer->render($ro);
        $expected = '{
    "_links": {
        "self": {
            "href": "/task"
        }
    }
}
';
        $this->assertSame($expected, $hal);
    }

    public function testHalRendererWithParam()
    {
        $halRenderer = new HalRenderer(new AnnotationReader, new FakeRouter);
        $ro = new Task;
        $ro->uri = new Uri('app://self/task?id=1');
        $ro->uri->method = 'get';
        $ro = $ro->onGet(1);
        $hal = $halRenderer->render($ro);
        $expected = '{
    "_links": {
        "self": {
            "href": "/task/1"
        }
    }
}
';
        $this->assertSame($expected, $hal);
        $location = $ro->headers['Location'];
        $expected = '/task/10';
        $this->assertSame($expected, $location);
    }
}

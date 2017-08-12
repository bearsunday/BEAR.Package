<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\Package\Provide\Representation\HalRenderer;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\HelloWorld\Resource\App\Task;

class HalRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    public function setUp()
    {
        $this->resource = (new AppInjector('FakeVendor\HelloWorld', 'hal-app'))->getInstance(ResourceInterface::class);
    }

    public function testRender()
    {
        $ro = $this->resource->get->uri('app://self/user')->withQuery(['id' => 1, 'type' => 'type_a'])->eager->request();
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
        $result = (string) $ro;
        $expect = '{
    "_embedded": {
        "user": {
            "id": "1",
            "friend_id": "f1",
            "org_id": "o1"
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
        $result = (string) $ro;
        $expect = '';
        $this->assertSame($expect, $result);
    }

    public function testHalRendererNoParam()
    {
        $halRenderer = new HalRenderer(new AnnotationReader, new FakeRouter, $this->resource);
        $ro = new Task;
        $ro->onPost();
        $ro->uri = new Uri('app://self/task');
        $ro->uri->method = 'post';
        $hal = $halRenderer->render($ro);
        $expected = '{
    "dummy_not_for_rendering": "1",
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
        $halRenderer = new HalRenderer(new AnnotationReader, new FakeRouter, $this->resource);
        $ro = new Task;
        $ro->uri = new Uri('app://self/task?id=1');
        $ro->uri->method = 'post';
        $ro = $ro->onPost(1);
        $hal = $halRenderer->render($ro);
        $expected = '{
    "dummy_not_for_rendering": "1",
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

    public function test201Created()
    {
        $ro = $this->resource->post->uri('app://self/post')->eager->request();
        /* @var $ro \BEAR\Resource\ResourceObject */
        $result = (string) $ro;
        $expect = '{
    "id": "10",
    "name": "user_10",
    "_links": {
        "self": {
            "href": "/post?id=10"
        },
        "curies": [
            {
                "href": "http://api.example.com/docs/{rel}",
                "name": "ht",
                "templated": true
            }
        ],
        "ht:comment": {
            "href": "/comments/?id=10"
        },
        "ht:category": {
            "href": "/category/?id=10"
        },
        "test": {
            "href": "/test"
        }
    }
}
';
        $this->assertSame($expect, $result);
        $this->assertSame(201, $ro->code);
        $this->assertSame('/post?id=10', $ro->headers['Location']);
    }

    public function testLinksAlreadyExists()
    {
        $ro = $this->resource->get->uri('app://self/link')->eager->request();
        $result = (string) $ro;
        $expect = '{
    "message": "Welcome",
    "_links": {
        "self": {
            "href": "/"
        },
        "curies": [
            {
                "href": "http://localhost:8080/docs/{?rel}",
                "name": "pt",
                "templated": true
            }
        ],
        "pt:todo": {
            "href": "/todo"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }}

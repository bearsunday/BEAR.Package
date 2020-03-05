<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\AppInjector;
use BEAR\Package\Exception\LocationHeaderRequestException;
use BEAR\Resource\HalLink;
use BEAR\Resource\HalRenderer;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Uri;
use Doctrine\Common\Annotations\AnnotationReader;
use FakeVendor\HelloWorld\Resource\App\Task;
use PHPUnit\Framework\TestCase;

class HalRendererTest extends TestCase
{
    /**
     * @var ResourceInterface
     */
    private $resource;

    protected function setUp() : void
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
        $ro = $this->resource->get->uri('app://self/emb?id=1')->eager->request();
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
            "href": "/emb?id=1"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testNoEmbededLinksWhenSchemaIsDifferent()
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
        $expect = '{
    "GET": []
}
';
        $this->assertSame($expect, $result);
    }

    public function testHalRendererNoParam()
    {
        $halRenderer = new HalRenderer(new AnnotationReader, new HalLink(new RouterReverseLink(new FakeRouter)));
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
        $halRenderer = new HalRenderer(new AnnotationReader, new HalLink(new RouterReverseLink(new FakeRouter)));
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

    public function test201CreatedWithNoQuery()
    {
        $ro = $this->resource->post->uri('app://self/post?uri=/post')->eager->request();
        assert($ro instanceof ResourceObject);
        $result = (string) $ro;
        $this->assertSame(201, $ro->code);
        $this->assertSame('/post', $ro->headers['Location']);
    }

    public function testCreatedResourceAnnotationButFailed()
    {
        /** @var \BEAR\Resource\ResourceObject $ro */
        $ro = $this->resource->post->uri('app://self/post?code=500');
        $result = (string) $ro;
        $expect = '{
    "_links": {
        "self": {
            "href": "/post?code=500"
        }
    }
}
';
        $this->assertSame($expect, $result);
    }

    public function testCreatedResourceInvaliUri()
    {
        $ro = $this->resource->post->uri('app://self/post?uri=__INVALID_*+')();

        $errNo = $errStr = '';
        set_error_handler(function (int $no, string $str) use (&$errNo, &$errStr) {
            $errNo = $no;
            $errStr = $str;
        });
        assert($ro instanceof ResourceObject);
        $ro->__toString();

        $this->assertSame(512, $errNo);
        $this->assertStringContainsString(LocationHeaderRequestException::class, $errStr);

        restore_error_handler();

        $this->assertSame('', $ro->view);
        $this->assertSame(500, $ro->code);
    }

    public function testLinksAlreadyExists()
    {
        $ro = $this->resource->get->uri('app://self/hal')->eager->request();
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
    }
}

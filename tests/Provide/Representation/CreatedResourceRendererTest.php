<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\AppInjector;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\Resource\App\Post;
use PHPUnit\Framework\TestCase;

use function assert;

class CreatedResourceRendererTest extends TestCase
{
    private CreatedResourceRenderer $renderer;
    private Post $ro;

    protected function setUp(): void
    {
        $resource = (new AppInjector('FakeVendor\HelloWorld', 'hal-app'))->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $post = $resource->post('app://self/post');
        assert($post instanceof Post);
        $this->ro = $post;
        $this->renderer = new CreatedResourceRenderer(new FakeRouter(), $resource);
    }

    public function testRender(): ResourceObject
    {
        $view = $this->renderer->render($this->ro);
        $expected = '{
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
        $this->assertSame($expected, $view);
        $this->assertSame($expected, $this->ro->view);

        return $this->ro;
    }

    /** @depends testRender */
    public function testReverseRoutedHeader(ResourceObject $ro): void
    {
        $this->assertSame('/task/10', $ro->headers['Location']);
    }
}

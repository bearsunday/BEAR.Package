<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Representation;

use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\ResourceObject;
use FakeVendor\HelloWorld\Resource\App\Post;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function assert;
use function dirname;
use function is_string;

class CreatedResourceRendererTest extends TestCase
{
    private CreatedResourceRenderer $renderer;
    private Post $ro;

    protected function setUp(): void
    {
        $appDIr = dirname(__DIR__, 2) . '/Fake/fake-app';
        $resource = Injector::getInstance('FakeVendor\HelloWorld', 'hal-app', $appDIr)->getInstance(ResourceInterface::class);
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
        $this->assertJsonStringEqualsJsonString($expected, $view);
        $this->assertJsonStringEqualsJsonString($expected, (string) $this->ro->view);

        return $this->ro;
    }

    #[Depends('testRender')]
    public function testReverseRoutedHeader(ResourceObject $ro): void
    {
        $this->assertSame('/task/10', $ro->headers['Location']);
    }

    public function testReverseMatchDropsNonStringQueryKeys(): void
    {
        // parse_str() casts the numeric key "0" to int; QueryParamNormalizer::normalize() skips it,
        // leaving no params to reverse-route so the original URI is returned unchanged.
        $getReverseMatchedLink = new ReflectionMethod(CreatedResourceRenderer::class, 'getReverseMatchedLink');
        $uri = '/task?0=zero';
        $result = $getReverseMatchedLink->invoke($this->renderer, $uri);
        assert(is_string($result));
        $this->assertSame($uri, $result);
    }
}

<?php

namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;
use BEAR\Package\Mock\ResourceObject\MockResource;

class HalRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HalRenderer
     */
    private $halRenderer;

    /**
     * @var ResourceObject
     */
    private $resource;

    protected function setUp()
    {
        $this->halRenderer = new HalRenderer(new HalFactory(new SchemeUriMapper));
        $this->resource = new MockResource;
        $this->resource->uri = 'dummy://self/index';

    }

    public function testNew()
    {
        $this->assertInstanceOf('\BEAR\Package\Provide\ResourceView\HalRenderer', $this->halRenderer);
    }

    public function testRender()
    {
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertSame("application/hal+json; charset=UTF-8", $this->resource->headers['content-type']);

        return $this->resource;
    }

    /**
     * @depends testRender
     */
    public function testRenderView(ResourceObject $resource)
    {
        $this->assertContains('"greeting": "hello"', $resource->view);
    }

    public function testRenderBodyIsScalar()
    {
        $this->resource->body = 'hello';
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertContains('"value": "hello"', $this->resource->view);
    }

    public function testRenderHasLink()
    {
        $this->resource->links = ['rel1' => ['href' => 'page://self/rel1']];
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $links = '"_links": {
        "self": {
            "href": "http://localhost/dummy/index/"
        },
        "rel1": {
            "href": "http://localhost/page/rel1/"
        }
    }';
        $this->assertContains($links, $this->resource->view);
    }

    /**
     * @expectedException \BEAR\Package\Provide\ResourceView\Exception\HrefNotFound
     */
    public function testRenderInvalidLink()
    {
        $this->resource->links = ['rel1' => 'page://self/rel1'];
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);

    }

    public function testBodyHasRequest()
    {
        $request = require $_ENV['TEST_DIR'] . '/scripts/instance/request.php';
        $request->set(new MockResource, 'nop://mock', 'get', ['a'=>1, 'b'=>2]);
        $this->resource->body['req'] = $request;
        $this->resource->setRenderer($this->halRenderer);
        $this->halRenderer->render($this->resource);
        $this->assertContains('"greeting": "hello"', $this->resource->view);
    }
}

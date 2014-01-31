<?php

namespace BEAR\Package\Provide\WebResponse;

use BEAR\Resource\BodyArrayAccessTrait;
use BEAR\Resource\ResourceObject;
use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Provide\ResourceView\HalRenderer;
use BEAR\Package\Provide\ResourceView\HalFactory;
use BEAR\Resource\Uri;
use BEAR\Package\Provide\ResourceView\SchemeUriMapper;
use BEAR\Resource\RenderTrait;

class Ok extends ResourceObject
{
    public $code = 200;
    public $headers = [];
    public $body = 'ok_body';
    public $view = 'ok_view';
}

/**
 * Test class for Annotation.
 */
class HttpFoundationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpFoundation
     */
    private $response;

    protected function setUp()
    {
        $this->response = new HttpFoundation(new ConsoleOutput);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Sunday\Extension\WebResponse\ResponseInterface', $this->response);
    }

    public function testSendWeb()
    {
        $this->response->setIsCli(false);
        $response = new Ok;
        $response->view = "follow the first one.";
        $this->response->setResource($response)->setIsCli(false)->render()->send();
        $this->expectOutputString($response->view);
    }

    public function testSendCli()
    {
        $this->response->setIsCli(false);
        ob_start();
        $response = new Ok;
        $response->body = 'this is body';
        $this->response->setResource($response)->setIsCli(true)->render()->send();
        $ob = ob_get_clean();
        $this->assertContains('this is body', $ob);
    }

    public function testRender()
    {
        $this->response->setIsCli(false);
        $response = new Ok;
        $response->uri = 'dummy://self/index';
        $render = new HalRenderer(new HalFactory(new SchemeUriMapper));
        ob_start();
        $this->response->setResource($response)->render($render)->send();
        $ob = ob_get_clean();
        $this->assertContains('http://localhost/dummy/index/', $ob);
    }

    public function testWithWeavedResource()
    {
        $this->response->setIsCli(false);
        $response = new Ok;
        $response->uri = 'dummy://self/index';
        $weavedResource = new Weaver($response, new Bind);
        $render = new HalRenderer(new HalFactory(new SchemeUriMapper));
        ob_start();
        $this->response->setResource($weavedResource)->render($render)->send();
        $ob = ob_get_clean();
        $this->assertContains('http://localhost/dummy/index', $ob);
    }

    /**
     * @expectedException \BEAR\Sunday\Exception\InvalidResourceType
     */
    public function testInvalidResource()
    {
        $this->response->setResource(new \stdClass);
    }
}

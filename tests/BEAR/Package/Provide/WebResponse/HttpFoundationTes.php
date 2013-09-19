<?php

namespace BEAR\Package\Provide\WebResponse;

use Ray\Aop\Weaver;
use Ray\Aop\Bind;
use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
use BEAR\Package\Provide\ResourceView\HalRenderer;
use BEAR\Resource\AbstractObject;
use BEAR\Package\Provide\WebResponse\HttpFoundation;

class Ok extends AbstractObject
{
    public $code = 200;
    public $headers = [];
    public $body = 'ok';
    public $view = 'ok';
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
        $this->response->setResource($response)->render()->send();
        $this->expectOutputString($response->view);
    }

    public function testSendCli()
    {
        $response = new Ok;
        $response->view = "follow the first one.";
//        ob_start();
        $this->response->setResource($response)->render()->send();
        $ob = ob_get_clean();
        $this->assertContains($response->body, $ob);
    }

    public function testRender()
    {
        $this->response->setIsCli(false);
        $response = new Ok;
        $response->uri = 'dummy://self/index';
        $render = new HalRenderer;
//        ob_start();
        $this->response->setResource($response)->render($render)->send();
        $ob = ob_get_clean();
        $this->assertContains('"href": "dummy://self/index"', $ob);

    }

    public function testWithWeavedResource()
    {
        $this->response->setIsCli(false);
        $response = new Ok;
        $response->uri = 'dummy://self/index';
        $weavedResource = new Weaver($response, new Bind);
        $render = new HalRenderer;
//        ob_start();
        $this->response->setResource($weavedResource)->render($render)->send();
        $ob = ob_get_clean();
        $this->assertContains('"href": "dummy://self/index"', $ob);
    }

    /**
     * @expectedException \BEAR\Sunday\Exception\InvalidResourceType
     */
    public function testInvalidResource()
    {
        $this->response->setResource(new \stdClass);
    }
}

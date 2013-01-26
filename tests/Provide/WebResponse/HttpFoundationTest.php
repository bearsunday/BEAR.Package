<?php

namespace BEAR\Package\tests\Provide\WebResponse;

use BEAR\Package\Provide\ConsoleOutput\ConsoleOutput;
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
        parent::setUp();
        $this->response = new HttpFoundation(new ConsoleOutput);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Sunday\Extension\WebResponse\ResponseInterface', $this->response);
    }

    public function testOutput()
    {
        $response = new Ok;
        $response->body = '';
        ob_start();
        $this->response->setResource($response)->send();
        $ob = ob_get_clean();
        $this->assertTrue(is_string($ob));
    }
}

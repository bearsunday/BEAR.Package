<?php

namespace BEAR\Sunday\Tests;

use BEAR\Sunday\Output\Console;

use BEAR\Package\Web\SymfonyResponse as Response;
use BEAR\Sunday\Resource\Page\Ok;
/**
 * Test class for Annotation.
 */
class SymfonyResponseTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->response = new Response(new Console);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Web\SymfonyResponse', $this->response);
    }

    public function test_Output()
    {
        $response = new Ok;
        $response->body = '';
        ob_start();
        $this->response->setResource($response)->send();
        $ob = ob_get_clean();
        $this->assertTrue(is_string($ob));
    }

    public function test_Prepare_Output()
    {
        $response = new Ok;
        $response->body = '';
        ob_start();
        $this->response->setResource($response)->prepare()->send();
        $ob = ob_get_clean();
        $this->assertTrue(is_string($ob));
    }

}

<?php

namespace BEAR\Sunday\Provide\Transfer;

use BEAR\Package\Provide\Transfer\CliResponder;
use BEAR\Sunday\Fake\Resource\FakeResource;
use FakeVendor\HelloWorld\Resource\Page\Index;

class CliResponderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpResponder
     */
    private $responder;

    public function setUp()
    {
        parent::setUp();
        $this->responder = new CliResponder;
    }

    public function testTransfer()
    {
        $ro = (new Index)->onGet();
        ob_start();
        $ro->transfer($this->responder, []);
        $actual =  ob_get_clean();
        $expect = '200 OK

{"greeting":"Hello BEAR.Sunday"}
';
        $this->assertSame($expect, $actual);
    }
}

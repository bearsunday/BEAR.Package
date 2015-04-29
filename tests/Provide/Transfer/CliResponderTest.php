<?php

namespace BEAR\Sunday\Provide\Transfer;

use BEAR\Package\Provide\Transfer\CliResponder;
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
        $ro->headers['X-BEAR-VERSION'] = 'Sunday';
        ob_start();
        $ro->transfer($this->responder, []);
        $actual =  ob_get_clean();
        $expect = <<< EOT
200 OK
X-BEAR-VERSION: Sunday
content-type: application/json

{"greeting":"Hello BEAR.Sunday"}

EOT;
        $this->assertSame($expect, $actual);
    }
}

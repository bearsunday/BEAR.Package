<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use FakeVendor\HelloWorld\Resource\Page\Index;
use PHPUnit\Framework\TestCase;

class CliResponderTest extends TestCase
{
    /**
     * @var CliResponder
     */
    private $responder;

    protected function setUp()
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
        $actual = ob_get_clean();
        $expect = <<< 'EOT'
200 OK
X-BEAR-VERSION: Sunday
content-type: application/json

{"greeting":"Hello BEAR.Sunday"}
EOT;
        $this->assertSame($expect, $actual);
    }
}

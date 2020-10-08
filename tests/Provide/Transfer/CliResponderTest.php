<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use BEAR\Sunday\Provide\Transfer\ConditionalResponse;
use BEAR\Sunday\Provide\Transfer\Header;
use FakeVendor\HelloWorld\Resource\Page\Index;
use PHPUnit\Framework\TestCase;

use function assert;
use function is_string;
use function ob_get_clean;
use function ob_start;

class CliResponderTest extends TestCase
{
    /** @var CliResponder */
    private $responder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responder = new CliResponder(new Header(), new ConditionalResponse());
    }

    public function testTransfer(): void
    {
        $ro = (new Index())->onGet();
        assert(is_string((string) $ro));
        $ro->headers['X-BEAR-VERSION'] = 'Sunday';
        ob_start();
        $ro->transfer($this->responder, []);
        $actual = ob_get_clean();
        $expect = <<< 'EOT'
200 OK
Content-Type: application/json
X-BEAR-VERSION: Sunday

{"greeting":"Hello BEAR.Sunday"}
EOT;
        $this->assertSame($expect, $actual);
    }
}

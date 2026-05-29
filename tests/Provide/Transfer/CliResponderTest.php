<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Transfer;

use BEAR\Sunday\Provide\Transfer\ConditionalResponse;
use BEAR\Sunday\Provide\Transfer\Header;
use FakeVendor\HelloWorld\Resource\Page\Index;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

class CliResponderTest extends TestCase
{
    private CliResponder $responder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responder = new CliResponder(new Header(), new ConditionalResponse());
    }

    public function testTransfer(): void
    {
        $ro = (new Index())->onGet();
        $ro->headers['X-BEAR-VERSION'] = 'Sunday';
        ob_start();
        $ro->transfer($this->responder, []);

        $output = ob_get_clean();
        $this->assertNotFalse($output);
        /** @var non-empty-string $output */

        $expect = <<< 'EOT'
200 OK
Content-Type: application/json
X-BEAR-VERSION: Sunday

{"greeting":"Hello BEAR.Sunday"}
EOT;
        $this->assertStringContainsString('200 OK', $output);
        $this->assertStringContainsString('Content-Type: application/json', $output);
        $this->assertStringContainsString('X-BEAR-VERSION: Sunday', $output);
        $this->assertStringContainsString('{"greeting":"Hello BEAR.Sunday"}', $output);
    }
}

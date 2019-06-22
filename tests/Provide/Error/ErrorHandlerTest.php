<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\Provide\Transfer\FakeHttpResponder;
use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Provide\Transfer\ConditionalResponse;
use BEAR\Sunday\Provide\Transfer\Header;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ErrorHandlerTest extends TestCase
{
    /**
     * @var ErrorHandler
     */
    private $handler;

    /**
     * @var FakeHttpResponder
     */
    private $responder;

    protected function setUp() : void
    {
        parent::setUp();
        $this->responder = new FakeHttpResponder(new Header, new ConditionalResponse);
        $this->handler = new ErrorHandler($this->responder, new ErrorLogger(new NullLogger, new AppMeta('FakeVendor\HelloWorld')), new ProdVndErrorPageFactory());
    }

    public function testHandle()
    {
        $e = new \LogicException('msg');
        $request = new RouterMatch;
        list($request->method, $request->path, $request->query) = ['get', '/', []];
        $handler = $this->handler->handle($e, $request);
        $this->assertInstanceOf(ErrorHandler::class, $handler);

        return $handler;
    }

    /**
     * @depends testHandle
     */
    public function testTransfer(ErrorHandler $handler)
    {
        $handler->transfer();
        $this->assertSame(500, FakeHttpResponder::$code);
        $this->assertSame(['content-type' => 'application/vnd.error+json'], FakeHttpResponder::$headers);
        $this->assertSame('{
    "message": "Internal Server Error",
    "logref": "{logref}"
}
', FakeHttpResponder::$content);
    }
}

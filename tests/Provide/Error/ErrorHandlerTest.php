<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\AppMeta\AppMeta;
use BEAR\Package\FakeLogger;
use BEAR\Package\Provide\Transfer\FakeHttpResponder;
use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Provide\Transfer\ConditionalResponse;
use BEAR\Sunday\Provide\Transfer\Header;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function assert;

class ErrorHandlerTest extends TestCase
{
    private ErrorHandler $handler;
    private FakeHttpResponder $responder;
    private FakeLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->responder = new FakeHttpResponder(new Header(), new ConditionalResponse());
        $this->logger = new FakeLogger();
        $this->handler = new ErrorHandler($this->responder, new ErrorLogger($this->logger, new AppMeta('FakeVendor\HelloWorld')), new ProdVndErrorPageFactory());
    }

    public function testHandleError(): ErrorHandler
    {
        $e = new LogicException('msg', 500);
        $request = new RouterMatch();
        [$request->method, $request->path, $request->query] = ['get', '/', []];
        $handler = $this->handler->handle($e, $request);
        $this->assertSame('error', $this->logger->called);
        assert($handler instanceof ErrorHandler);
        $this->handler->transfer();

        return $handler;
    }

    /** @depends testHandle */
    public function testTransfer(ErrorHandler $handler): void
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

    public function testHandleDebug(): void
    {
        $e = new RuntimeException('msg', 0);
        $request = new RouterMatch();
        $this->handler->handle($e, $request);
        $this->assertSame('debug', $this->logger->called);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Error;

use BEAR\Package\FakeLogger;
use BEAR\Package\Provide\Transfer\FakeHttpResponder;
use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Sunday\Extension\Router\RouterMatch;
use BEAR\Sunday\Provide\Transfer\ConditionalResponse;
use BEAR\Sunday\Provide\Transfer\Header;
use LogicException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

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
        $this->handler = new ErrorHandler($this->responder, new ErrorLogger($this->logger, new NullLogRefWriter()), new ProdVndErrorPageFactory());
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

    #[Depends('testHandleError')]
    public function testTransfer(ErrorHandler $handler): void
    {
        $handler->transfer();
        $this->assertSame(500, FakeHttpResponder::$code);
        $this->assertSame(['content-type' => 'application/vnd.error+json'], FakeHttpResponder::$headers);
        $this->assertJsonStringEqualsJsonString('{
    "message": "Internal Server Error",
    "logref": "{logref}"
}
', FakeHttpResponder::$content);
    }

    public function testHandleDebug(): void
    {
        $e = new ResourceNotFoundException('/__not_found__');
        $request = new RouterMatch();
        $this->handler->handle($e, $request);
        $this->assertSame('debug', $this->logger->called);
    }
}

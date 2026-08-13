<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Exception\InvalidRequestUriException;
use BEAR\Package\Provide\Error\Status;
use PHPUnit\Framework\TestCase;

class WebRouterTest extends TestCase
{
    private WebRouter $router;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = new WebRouter('page://self', new HttpMethodParams());
    }

    public function testMatchRoot(): void
    {
        $global = [
            '_GET' => [],
            '_POST' => [],
        ];
        $server = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/',
        ];
        $request = $this->router->match($global, $server);
        $this->assertSame('get', $request->method);
        $this->assertSame('page://self/', $request->path);
        $this->assertSame([], $request->query);
    }

    public function testMatchWithQuery(): void
    {
        $global = [
            '_GET' => ['id' => '1'],
            '_POST' => [],
        ];
        $server = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/?id=1',
        ];
        $request = $this->router->match($global, $server);
        $this->assertSame('get', $request->method);
        $this->assertSame('page://self/', $request->path);
        $this->assertSame(['id' => '1'], $request->query);
    }

    public function testGenerate(): void
    {
        $actual = $this->router->generate('', []);
        $this->assertFalse((bool) $actual);
    }

    /**
     * PHP accepts a request line parse_url() reads no path from, and the answer must not depend
     * on whether assertions are compiled in: it is a client error both ways.
     */
    public function testPathlessRequestUriIsABadRequest(): void
    {
        $global = ['_GET' => [], '_POST' => []];
        $server = ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '//'];

        $this->expectException(InvalidRequestUriException::class);
        $this->expectExceptionMessage('No path in request URI "//"');
        $this->router->match($global, $server);
    }

    /** A pathless URI answers 400, not the 500 a RuntimeException would give. */
    public function testPathlessRequestUriMapsTo400(): void
    {
        try {
            $this->router->match(['_GET' => [], '_POST' => []], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '///']);
        } catch (InvalidRequestUriException $e) {
            $this->assertSame(400, (new Status($e))->code);

            return;
        }

        $this->fail('A pathless request URI must be rejected');
    }
}

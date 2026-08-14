<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Exception\InvalidRequestUriException;
use BEAR\Package\FakeErrorRouter;
use BEAR\Package\FakeWebRouter;
use BEAR\Sunday\Provide\Router\WebRouter;
use LogicException;
use PHPUnit\Framework\TestCase;

use function is_bool;

class RouterCollectionTest extends TestCase
{
    private RouterCollection $routerCollection;

    protected function setUp(): void
    {
        $webRouter = new WebRouter('page://self');
        $fakeRouter = new FakeWebRouter('page://self', new HttpMethodParams());
        $this->routerCollection = (new RouterCollectionProvider($webRouter, $fakeRouter))->get();

        parent::setUp();
    }

    public function testMatch(): void
    {
        $globals = [
            '_GET' => [],
            '_POST' => ['title' => 'hello'],
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment',
        ];
        $request = $this->routerCollection->match($globals, $server);
        $this->assertSame('post', $request->method);
        $this->assertSame('page://self/blog/PC6001', $request->path);
    }

    public function testNotFound(): void
    {
        $globals = [
            '_GET' => [],
            '_POST' => [],
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/',
        ];
        $routerCollection = new RouterCollection([]);
        $matchUri = (string) $routerCollection->match($globals, $server);
        $expected = 'get page://self/__route_not_found';
        $this->assertSame($expected, $matchUri);
    }

    public function testGenerate(): void
    {
        $uri = $this->routerCollection->generate('/blog', ['id' => 1]);
        $expected = 'page://self/generated-uri';
        if (is_bool($uri)) {
            throw new LogicException();
        }

        $this->assertSame($expected, $uri);
    }

    public function testGenerateFalse(): void
    {
        $uri = $this->routerCollection->generate('/blog', []);
        if (! is_bool($uri)) {
            throw new LogicException();
        }

        $this->assertFalse($uri);
    }

    public function testRouterError(): void
    {
        $globals = [
            '_GET' => [],
            '_POST' => [],
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/',
        ];
        $routerCollection = new RouterCollection([new FakeErrorRouter()]);
        $matchUri = (string) $routerCollection->match($globals, $server);
        $expected = 'get page://self/__route_not_found';
        $this->assertSame($expected, $matchUri);
    }

    /**
     * A collection is what an application with a primary router uses, and the primary router
     * returns NullMatch for a request line it holds no route for, so the WebRouter behind it is
     * the one that reads the URI. Its client error has to reach the caller: route-not-found
     * answers 404, and a malformed request line is a 400.
     *
     * FakeWebRouter is the package WebRouter with generate() overridden, so match() below is the
     * production one. The plain WebRouter name in this file is BEAR\Sunday's.
     */
    public function testClientErrorPropagates(): void
    {
        $routerCollection = new RouterCollection([new FakeWebRouter('page://self', new HttpMethodParams())]);

        $this->expectException(InvalidRequestUriException::class);
        $routerCollection->match(['_GET' => [], '_POST' => []], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '//']);
    }
}

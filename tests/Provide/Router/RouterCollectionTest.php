<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Router;

use BEAR\Package\FakeWebRouter;
use BEAR\Sunday\Provide\Router\WebRouter;
use PHPUnit\Framework\TestCase;

class RouterCollectionTest extends TestCase
{
    /**
     * @var RouterCollection
     */
    private $routerCollection;

    protected function setUp() : void
    {
        $webRouter = new WebRouter('page://self');
        $fakeRouter = new FakeWebRouter('page://self', new HttpMethodParams);
        $this->routerCollection = (new RouterCollectionProvider($webRouter, $fakeRouter))->get();
        parent::setUp();
    }

    public function testMatch()
    {
        $globals = [
            '_GET' => [],
            '_POST' => ['title' => 'hello']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
        ];
        $request = $this->routerCollection->match($globals, $server);
        $this->assertSame('post', $request->method);
        $this->assertSame('page://self/blog/PC6001', $request->path);
    }

    public function testNotFound()
    {
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/'
        ];
        $routerCollection = new RouterCollection([]);
        $matchUri = (string) $routerCollection->match([], $server);
        $expected = 'get page://self/__route_not_found';
        $this->assertSame($expected, $matchUri);
    }

    public function testGenerate()
    {
        $uri = $this->routerCollection->generate('/blog', ['id' => 1]);
        $expected = 'page://self/generated-uri';
        if (is_bool($uri)) {
            throw new \LogicException;
        }
        $this->assertSame($expected, $uri);
    }

    public function testGenerateFalse()
    {
        $uri = $this->routerCollection->generate('/blog', []);
        if (! is_bool($uri)) {
            throw new \LogicException;
        }
        $this->assertFalse($uri);
    }
}

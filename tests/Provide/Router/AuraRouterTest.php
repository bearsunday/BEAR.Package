<?php

namespace Provide\Router;

use Aura\Router\RouterFactory;
use BEAR\Package\Provide\Router\AuraRouter;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aura\Router\RouteCollection
     */
    private $routerAdapter;

    public function setUp()
    {
        parent::setUp();
        $this->routerAdapter = (new RouterFactory)->newInstance();
    }

    public function testMatch()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => '/blog']);
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
            ],
            '_POST' => ['title' => 'hello']
        ];
        $router = new AuraRouter($this->routerAdapter);
        $request = $router->match($globals);
        $this->assertSame('post', $request->method);
        $this->assertSame('/blog', $request->path);
        $this->assertSame(['id' => 'PC6001', 'title' => 'hello'], $request->query);
    }

    public function testMethodOverrideField()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT', 'title' => 'hello']
        ];
        $router = new AuraRouter($this->routerAdapter);
        $request = $router->match($globals);
        $this->assertSame('put', $request->method);
        $this->assertSame(['id' => 'PC6001', 'title' => 'hello'], $request->query);
    }

    public function testMethodOverrideHeader()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT']
        ];
        $router = new AuraRouter($this->routerAdapter);
        $request = $router->match($globals);
        $this->assertSame('delete', $request->method);
        $this->assertSame(['id' => 'PC6001'], $request->query);
    }

    public function testNotMuch()
    {
        $this->routerAdapter
            ->addGet(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => 'http://localhost/not_much_uri',
            ],
            '_POST' => []
        ];
        $router = new AuraRouter($this->routerAdapter);
        $match = $router->match($globals);
        $this->assertFalse($match);
    }
}

<?php

namespace BEAR\Package\Provide\Router;

use Aura\Router\RouterFactory;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Aura\Router\RouteCollection
     */
    private $routerAdapter;

    /**
     * @var AuraRouter
     */
    private $auraRouter;

    public function setUp()
    {
        parent::setUp();
        $this->routerAdapter = (new RouterFactory)->newInstance();
        $this->auraRouter = new AuraRouter($this->routerAdapter, 'page://self', new HttpMethodParams);
    }

    public function testMatch()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => '/blog']);
        $globals = [
            '_POST' => ['title' => 'hello']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
        ];
        $request = $this->auraRouter->match($globals, $server);
        $this->assertSame('post', $request->method);
        $this->assertSame('page://self/blog', $request->path);
        $this->assertSame(['id' => 'PC6001', 'title' => 'hello'], $request->query);
    }

    public function testMethodOverrideField()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT', 'title' => 'hello']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
        ];
        $request = $this->auraRouter->match($globals, $server);
        $this->assertSame('put', $request->method);
        $this->assertSame(['id' => 'PC6001', 'title' => 'hello'], $request->query);
    }

    public function testMethodOverrideHeader()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment',
            'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'
        ];
        $request = $this->auraRouter->match($globals, $server);
        $this->assertSame('delete', $request->method);
        $this->assertSame(['id' => 'PC6001'], $request->query);
    }

    public function testNotMatch()
    {
        $this->routerAdapter
            ->addGet(null, '/blog/{id}')
            ->addValues(['path'  => 'blog']);
        $globals = [
            '_POST' => []
        ];
        $server = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => 'http://localhost/not_much_uri',
        ];
        $match = $this->auraRouter->match($globals, $server);
        $this->assertFalse($match);
    }

    public function testInvalidPath()
    {
        $globals = [
        ];
        $server = [
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => null
        ];
        $match = $this->auraRouter->match($globals, $server);
        $this->assertFalse($match);
    }
}

<?php

namespace Provide\Router;

use Aura\Router\RouterFactory;
use BEAR\Pacakge\Exception\RouteNotFoundException;
use BEAR\Package\Provide\Router\AuraRouter;
use BEAR\Package\Provide\Router\HttpMethodParams;
use BEAR\Package\Provide\Router\RouterCollection;
use BEAR\Sunday\Provide\Router\WebRouter;

class RouterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouterCollection
     */
    private $routerCollection;

    protected function setUp()
    {
        $routerAdapter = (new RouterFactory)->newInstance();
        $auraRouter = new AuraRouter($routerAdapter, 'page://self', new HttpMethodParams);
        $webRouter = new WebRouter('page://self', new HttpMethodParams);
        $this->routerCollection = new RouterCollection([$auraRouter, $webRouter]);
        $routerAdapter
            ->add('/blog', '/blog/{id}')
            ->addValues(['path'  => '/blog']);
        parent::setUp();
    }
    public function testMatch()
    {
        $globals = [
            '_POST' => ['title' => 'hello']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
        ];
        $request = $this->routerCollection->match($globals, $server);
        $this->assertSame('post', $request->method);
        $this->assertSame('page://self/blog', $request->path);
        $this->assertSame(['action' => '/blog', 'id' => 'PC6001', 'title' => 'hello'], $request->query);
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
        $expected = '/blog/1';
        $this->assertSame($expected, $uri);
    }

    public function testGenerateFaild()
    {
        $uri = $this->routerCollection->generate('/invalid', ['id' => 1]);
        $this->assertFalse($uri);
    }
}

<?php

namespace Provide\Router;

use Aura\Router\RouterFactory;
use BEAR\Package\Provide\Router\AuraRouter;
use BEAR\Package\Provide\Router\HttpMethodParams;
use BEAR\Package\Provide\Router\RouterCollection;
use BEAR\Sunday\Provide\Router\WebRouter;

class RouterCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $routerAdapter = (new RouterFactory)->newInstance();
        $auraRouter = new AuraRouter($routerAdapter, 'page://self', new HttpMethodParams);
        $webRouter = new WebRouter('page://self', new HttpMethodParams);
        $routerCollection = new RouterCollection([$auraRouter, $webRouter]);
        $routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(['path'  => '/blog']);
        $globals = [
            '_POST' => ['title' => 'hello']
        ];
        $server = [
            'REQUEST_METHOD' => 'POST',
            'REQUEST_URI' => 'http://localhost/blog/PC6001?query=value#fragment'
        ];

        $request = $routerCollection->match($globals, $server);
        $this->assertSame('post', $request->method);
        $this->assertSame('page://self/blog', $request->path);
        $this->assertSame(['id' => 'PC6001', 'title' => 'hello'], $request->query);
    }
}

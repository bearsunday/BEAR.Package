<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Provide\Router\Adapter\AuraRouter;
use Aura\Router\RouterFactory;
use Aura\Router\Router;

class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Router
     */
    protected $routerAdapter;

    protected function setUp()
    {
        $this->routerAdapter = (new RouterFactory)->newInstance();
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\Router\Adapter\AuraRouter', new AuraRouter($this->routerAdapter));
    }

    public function testMatch()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(
                [
                    'path'  => 'blog'
                ]
            );
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
            ],
            '_POST' => ['title' => 'hello']
        ];
        $router = new AuraRouter($this->routerAdapter);
        list($method, $pageUri, $query) = $router->match('/blog/6001', $globals);
        $this->assertSame($method, 'post');
        $this->assertSame($pageUri, 'blog');
        $this->assertSame($query, ['id' => '6001', 'title' => 'hello']);
    }

    public function testMethodOverrideField()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(
                [
                    'path'  => 'blog'
                ]
            );
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT', 'title' => 'hello']
        ];
        $router = new AuraRouter($this->routerAdapter);
        list($method, , $query) = $router->match('/blog/6001', $globals);
        $this->assertSame($method, 'put');
        $this->assertSame($query, ['id' => '6001', 'title' => 'hello']);
    }

    public function testMethodOverrideHeader()
    {
        $this->routerAdapter
            ->addPost(null, '/blog/{id}')
            ->addValues(
            [
                'path'  => 'blog'
            ]
            );
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'PUT']
        ];
        $router = new AuraRouter($this->routerAdapter);
        list($method) = $router->match('/blog/6001', $globals);
        $this->assertSame($method, 'delete');
    }

    public function testNotMuch()
    {
        $this->routerAdapter
            ->addGet(null, '/blog/{id}')
            ->addValues(
            [
                'path'  => 'blog'
            ]
            );
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
            ],
            '_POST' => []
        ];
        $router = new AuraRouter($this->routerAdapter);
        $match = $router->match('/not_much_uri', $globals);
        $this->assertFalse($match);
    }

    public function estResourceMatch()
    {
        $this->routerAdapter->attachResource('blog', '/blog');
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
            ],
            '_POST' => []
        ];
        $router = new AuraRouter($this->routerAdapter);
        list($method, $pageUri, $query) = $router->match('/blog/6001', $globals);
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, 'blog');
        $this->assertSame($query, ['id' => '6001']);
    }
}

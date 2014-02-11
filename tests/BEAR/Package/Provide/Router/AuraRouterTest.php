<?php

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Provide\Router\AuraRouter;
use Aura\Router\Map;
use Aura\Router\DefinitionFactory;
use Aura\Router\RouteFactory;

/**
 * Test class for Pager.
 */
class AuraRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuraRouter
     */
    private $router;

    /**
     * @var \Aura\Router\Map
     */
    private $map;

    protected function setUp()
    {
        parent::setUp();
        $this->router = new AuraRouter;
        $this->map = new Map(new DefinitionFactory, new RouteFactory);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Sunday\Extension\Router\RouterInterface', $this->router);
    }

    public function testSimpleMatch()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_GET' => [],
            '_POST' => [],
        ];
        $this->router->setGlobals($globals);
        $match = $this->router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, 'this/is/my/path');
        $this->assertSame($query, []);
    }

    public function testSimpleMatchWithAuraRouter()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_GET' => ['name' => 'foo'],
            '_POST' => [],
        ];
        $this->map->add('my_path', '/this/is/my/path', [
            'values' => [
                'path'  => 'this/is/my/path'
            ],
        ]);

        $router = new AuraRouter($this->map);
        $router->setGlobals($globals);
        $match = $router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, 'this/is/my/path');
        $this->assertSame($query, ['name' => 'foo']);
    }

    public function testCorrectMethodNeeded()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/bar'
            ],
            '_GET' => [],
            '_POST' => [],
        ];
        $this->map->add('bar', '/bar', [
                'values' => [
                    'method'=> 'put',
                    'path'  => 'this/is/my/path'
                ],
            ]);
        $router = new AuraRouter($this->map);
        $router->setGlobals($globals);
        $match = $router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'put');
        $this->assertSame($pageUri, 'this/is/my/path');
        $this->assertSame($query, []);
    }

    public function testAdditionalParameters()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/archive/2004-10'
            ],
            '_GET' => ['name' => 'foo', 'month' => '2002-11'],
            '_POST' => [],
        ];
        $this->map->add('archive', '/archive/{:month}', [
                'parameter' =>[
                    'month' => '([0-9]{4}-[0-9]{2})'
                ],
                'values' => [
                    'method'=> 'get',
                    'path'  => 'archive'
                ],
            ]);
        $router = new AuraRouter($this->map);
        $router->setGlobals($globals);
        $match = $router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, 'archive');
        $this->assertEquals($query, ['month' => '2004-10', 'name' => 'foo']);
    }

    public function testPostEntityBodyHandling()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/archive/2004-10'
            ],
            '_GET' => [],
            '_POST' => ['name' => 'foo', 'month' => '2002-11'],
        ];
        $this->map->add('archive', '/archive/{:month}', [
                'parameter' =>[
                    'month' => '([0-9]{4}-[0-9]{2})'
                ],
                'values' => [
                    'path'  => 'archive'
                ],
            ]);
        $router = new AuraRouter($this->map);
        $router->setGlobals($globals);
        $match = $router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'post');
        $this->assertSame($pageUri, 'archive');
        $this->assertEquals($query, ['month' => '2004-10', 'name' => 'foo']);
    }

    public function testMethodOverrideGet()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_GET' => [AuraRouter::METHOD_OVERRIDE_GET => 'post']
        ];
        $this->router->setGlobals($globals);
        $match = $this->router->match();
        list($method) = $match;
        $this->assertSame('post', $method);
    }

    public function testMethodOverridePost()
    {
        $global = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_POST' => [AuraRouter::METHOD_OVERRIDE => 'get']
        ];
        $this->router->setGlobals($global);
        $match = $this->router->match();
        list($method) = $match;
        $this->assertSame('get', $method);
    }

    public function testMethodOverridePostByHeader()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/this/is/my/path',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE',
            ],
            '_POST' => [AuraRouter::METHOD_OVERRIDE => 'put']
        ];
        $this->router->setGlobals($globals);
        $match = $this->router->match();
        list($method) = $match;
        $this->assertSame('delete', $method);
    }

    public function testSettingArguments()
    {
        $argv = ['api.php','post', 'app://self/posts?title=hello&body="this is first post"'];
        $this->router->setArgv($argv);
        $result = $this->router->match();
        $expected = ['post', 'posts', ['title' => 'hello', 'body' => '"this is first post"']];
        $this->assertSame($expected, $result);
    }

    /**
     * @expectedException \BEAR\Resource\Exception\BadRequest
     */
    public function testSettingArgumentsInvalidArguments()
    {
        $argv = ['api.php','post'];
        $this->router->setArgv($argv);
        $this->router->match();
    }
}

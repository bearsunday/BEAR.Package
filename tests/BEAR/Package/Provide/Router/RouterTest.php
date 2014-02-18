<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\Router;

use BEAR\Package\Provide\Router\Adapter\AuraRouter;
use BEAR\Package\Provide\Router\Adapter\WebRouter;

class RouterTest extends \PHPUnit_Framework_TestCase
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
        $this->router = new Router(new WebRouter);
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
            '_GET' => []
        ];
        $this->router->setGlobals($globals);
        $match = $this->router->match();
        list($method, $pageUri, $query) = $match;
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, '/this/is/my/path');
        $this->assertSame($query, []);
    }

    public function testMethodOverridePost()
    {
        $global = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'get']
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
            '_POST' => [WebRouter::METHOD_FILED => 'put']
        ];
        $this->router->setGlobals($globals);
        $match = $this->router->match();
        list($method) = $match;
        $this->assertSame('put', $method);
    }

    public function testSettingArguments()
    {
        $argv = ['api.php','post', 'app://self/posts?title=hello&body="this is first post"'];
        $this->router->setArgv($argv);
        $result = $this->router->match();
        $expected = ['post', '/posts', ['title' => 'hello', 'body' => '"this is first post"']];
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

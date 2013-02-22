<?php

namespace BEAR\Package\tests\Provide\Router;

use BEAR\Package\Provide\Router\SymfonyRouter;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;

class SymfonyRouterTest extends \PHPUnit_Framework_TestCase
{
    private $router;
    private $context;
    private $collection;

    protected function setUp()
    {
        $this->router = new SymfonyRouter;
        $this->context = new RequestContext();
        $this->collection = new RouteCollection();
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\Router\SymfonyRouter', $this->router);
    }

    public function testSimpleMatch()
    {
        $route = new Route('/foo', ['_path' => 'this/is/my/path']);
        $this->collection->add('route_name', $route);
        $result = $this->getMatches('/foo');

        $this->assertEquals($result[0], 'get');
        $this->assertEquals($result[1], 'this/is/my/path');
    }

    public function testMethodTypes()
    {
        $this->context->setMethod('POST');
        $result = $this->getMatches('/foo');
        $this->assertEquals($result[0], 'post');
    }

    public function testMethodOverride()
    {
        $this->context->setParameters(['_method' => 'PUT']);
        $result = $this->getMatches('/foo');
        $this->assertEquals($result[0], 'put');
        $this->assertEquals($result[2], []);
    }

    public function testCorrectMethodNeeded()
    {
        $route = new Route('/bar', ['_path' => 'this/is/my/path'], ['_method' => 'put']);
        $this->collection->add('route_name', $route);
        $result = $this->getMatches('/bar');

        $this->assertEquals($result[1], '/bar');
    }

    public function testParametersAreCorrect()
    {
        $params = ['one' => 1, 'two' => 2];
        $this->context->setParameters($params);
        $result = $this->getMatches('/bar');
        $this->assertEquals($result[2], $params);
    }

    public function testFallbackWhenNoRoutePresent()
    {
        $result = $this->getMatches('/foo');
        $this->assertEquals($result[1], '/foo');
    }

    public function testAdditionalParameters()
    {
        $route = new Route(
            '/archive/{month}',
            ['someparam' => 'a param', '_path' => '/a-path'],
            ['month' => '[0-9]{4}-[0-9]{2}']
        );
        $this->context->setParameters(['one' => '1']);
        $this->collection->add('route_name', $route);
        $result = $this->getMatches('/archive/2004-10');
        $expected = ['someparam' => 'a param', 'month' => '2004-10', 'one' => '1'];
        $this->assertEquals($result[2], $expected);
    }

    public function testSettingArguments()
    {
        $data = ['api.php','post', 'app://self/posts?title=hello&body="this is first post"'];
        $this->router->setArgv($data);
        $this->router->setCollection($this->collection);
        $result = $this->router->match();
        $expected = ['post', '/posts', ['title' => 'hello', 'body' => '"this is first post"']];
        $this->assertEquals($expected, $result);
    }

    private function getMatches($route)
    {
        $this->router->setCollection($this->collection);
        $this->context->setPathInfo($route);
        $this->router->setContext($this->context);
        return $this->router->match();
    }
}

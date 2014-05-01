<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\Router\Adapter;

class WebRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuraRouter
     */
    protected $router;

    protected function setUp()
    {
        $this->router = new WebRouter;
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\Router\Adapter\WebRouter', $this->router);
    }

    public function testMatch()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
            ],
            '_GET' => []
        ];
        list($method, $pageUri, $query) = $this->router->match('/this/is/my/path', $globals);
        $this->assertSame($method, 'get');
        $this->assertSame($pageUri, '/this/is/my/path');
        $this->assertSame($query, []);
    }

    public function testMethodOverrideField()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/this/is/my/path'
            ],
            '_POST' => [AuraRouter::METHOD_FILED => 'GET']
        ];
        list($method, , ) = $this->router->match('/this/is/my/path', $globals);
        $this->assertSame($method, 'get');
    }

    public function testMethodOverrideHeader()
    {
        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'POST',
                'REQUEST_URI' => '/this/is/my/path',
                'HTTP_X_HTTP_METHOD_OVERRIDE' => 'DELETE'
            ],
            '_POST' => ['id => 1']
        ];
        list($method, $pagePath, $query) =  $this->router->match('/this/is/my/path', $globals);
        $this->assertSame($method, 'delete');
        $this->assertSame($pagePath, '/this/is/my/path');
        $this->assertSame($query, ['id => 1']);
    }
}

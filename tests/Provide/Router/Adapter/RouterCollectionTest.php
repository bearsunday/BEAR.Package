<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @package BEAR.Package
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */

namespace BEAR\Package\Provide\Router\Adapter;

use Aura\Router\RouterFactory;

class RouterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AuraRouter
     */
    protected $router;

    protected function setUp()
    {
        $this->router = new RouterCollection([new AuraRouter((new RouterFactory)->newInstance()), new WebRouter]);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Provide\Router\Adapter\RouterCollection', $this->router);
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

    public function testUnMatch()
    {
        $this->router = new RouterCollection([new AuraRouter((new RouterFactory)->newInstance())]);

        $globals = [
            '_SERVER' => [
                'REQUEST_METHOD' => 'GET',
            ],
            '_GET' => []
        ];
        $result = $this->router->match('/this/is/my/path', $globals);
        $this->assertFalse($result);
    }
}

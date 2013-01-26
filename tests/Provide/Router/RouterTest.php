<?php

namespace BEAR\Package\tests\Provide\Router;

use BEAR\Package\Provide\Router\MinRouter;

/**
 * Test class for Pager.
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->router = new MinRouter;
    }

    public function test_New()
    {
        $this->assertInstanceOf('BEAR\Sunday\Extension\Router\RouterInterface', $this->router);
    }
}

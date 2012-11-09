<?php

namespace BEAR\Package\Tests;

use BEAR\Package\Web\Router;

/**
 * Test class for Pager.
 */
class RouterTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->router = new Router;
    }

    public function test_New()
    {
        $this->assertInstanceOf('BEAR\Sunday\Web\RouterInterface', $this->router);
    }
}

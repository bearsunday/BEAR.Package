<?php

namespace BEAR\Package;

use BEAR\Resource\Exception\UriException;

class CliRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CliRouter
     */
    private $router;

    public function setUp()
    {
        $this->router = new CliRouter;
    }

    public function testMatch()
    {
        $globals = [
            'argv' => [
                'php',
                'get',
                'page://self/?name=bear'
            ]
        ];
        $request = $this->router->match($globals);
        $this->assertSame('get', $request->method);
        $this->assertSame('page://self/', $request->path);
        $this->assertSame(['name' => 'bear'], $request->query);
    }

    public function testInvalidUri()
    {
        $this->setExpectedException(UriException::class);
        $globals = [
            'argv' => [
                'php',
                'get',
                'invalid-uri'
            ]
        ];
        $this->router->match($globals);
    }
}

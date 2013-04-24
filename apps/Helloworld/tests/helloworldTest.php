<?php

namespace Helloworld;

use Ray\Di\Injector;
use Helloworld\Module\AppModule;

class HelloworldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        parent::setUp();
        if (!$this->resource) {
            $injector = Injector::create([new AppModule]);
            $this->resource = $injector->getInstance('BEAR\Resource\ResourceInterface');
        }
    }

    public function testPage()
    {
        // resource request
        $response = $this->resource->get->uri('page://self/hello')->withQuery(['name' => 'Sunday'])->eager->request();
        $this->assertSame(200, $response->code);
        $this->assertSame('Hello Sunday', $response->body);
    }

    public function testBasic()
    {
        // resource request
        $this->expectOutputString('Hello World !');
        require dirname(__DIR__) . '/public/basic.php';
    }

    public function testMin()
    {
        // resource request
        $this->expectOutputString('Hello World !');
        require dirname(__DIR__) . '/public/min.php';
    }

    public function testPull()
    {
        // resource request
        $this->expectOutputRegex("/^<html>\n/");
        require dirname(__DIR__) . '/public/pull.php';
    }
}

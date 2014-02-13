<?php

namespace Demo\Helloworld;

use Ray\Di\Injector;
use Demo\Helloworld\Module\AppModule;

class HelloworldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \BEAR\Resource\ResourceInterface
     */
    private $resource;

    protected function setUp()
    {
        static $resource;

        parent::setUp();
        if (! $resource) {
            $resource = Injector::create([new AppModule])->getInstance('BEAR\Resource\ResourceInterface');
        }
        $this->resource = $resource;
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
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/Hello';
        $this->expectOutputString('Hello World' . PHP_EOL);
        require dirname(__DIR__) . '/var/www/index.php';
    }

    public function testMin()
    {
        // resource request
        $this->expectOutputString('Hello World !' . PHP_EOL);
        require dirname(__DIR__) . '/var/www/min.php';
    }

    public function testPull()
    {
        // resource request
        $this->expectOutputRegex("/^<html>\n/");
        require dirname(__DIR__) . '/var/www/pull.php';
    }
}

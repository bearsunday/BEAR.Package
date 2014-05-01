<?php

namespace BEAR\Package\Provide\ResourceView;

use BEAR\Resource\ResourceObject;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use BEAR\Resource\Request;
use BEAR\Resource\Linker;
use BEAR\Resource\Invoker;
use BEAR\Resource\NamedParameter;
use BEAR\Resource\SignalParameter;
use BEAR\Resource\Param;
use Doctrine\Common\Annotations\AnnotationReader;
use BEAR\Resource\Logger;
use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;

final class Ok extends ResourceObject
{
    /**
     * Code
     *
     * @var int
     */
    public $code = 200;

    /**
     * Headers
     *
     * @var array
     */
    public $headers = [];

    /**
     * Body
     *
     * @var mixed
     */
    public $body = [
        'msg' => 'OK'
    ];

    /**
     * Get
     *
     * @return $this
     */
    public function onGet()
    {
        return $this;
    }
}

/**
 * Test class for JsonRenderer.
 */
class JsonRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testResource;

    protected function setUp()
    {
        parent::setUp();
        $invoker = new Invoker(
            new Linker(new AnnotationReader),
            new NamedParameter(
                new SignalParameter(
                    new Manager(new HandlerFactory, new ResultFactory, new ResultCollection),
                    new Param
                )
            ),
            new Logger
        );
        $request = new Request(
            $invoker,
            new Manager(new HandlerFactory, new ResultFactory, new ResultCollection)
        );
        $request->method = 'get';
        $this->testResource = new Ok;
        $request->ro = clone $this->testResource;
        $request->ro->uri = 'test://self/path/to/resource';
        $this->testResource['one'] = 1;
        $this->testResource['two'] = $request;
        $this->testResource->setRenderer(new JsonRenderer);
    }

    public function testRender()
    {
        // json render
        $result = (string) $this->testResource;
        $data = json_decode($result, true);
        $expected =[
            'msg' => 'OK',
            'one' => 1,
            'two' => [
                'msg' => 'OK'
            ]
        ];
        $this->assertSame($expected, $data);
    }
}

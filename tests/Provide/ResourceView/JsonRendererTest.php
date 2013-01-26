<?php

namespace BEAR\Package\tests\Provide\ResourceView;

use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use BEAR\Resource\Request;
use BEAR\Resource\Linker;
use BEAR\Resource\Invoker;
use BEAR\Resource\AbstractObject;
use BEAR\Package\Provide\ResourceView\JsonRenderer;

use Doctrine\Common\Annotations\AnnotationReader as Reader;

use Aura\Signal\Manager;
use Aura\Signal\HandlerFactory;
use Aura\Signal\ResultFactory;
use Aura\Signal\ResultCollection;

class RequestSample
{
    public function __toString()
    {
        return __CLASS__;
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
        $signal = new Manager(new HandlerFactory, new ResultFactory, new ResultCollection);
        $request = new Request(new Invoker(new Config(new Annotation(new Definition, new Reader)), new Linker(new Reader), $signal));
        $request->method = 'get';
        $this->testResource = new Ok;
        $request->ro = $this->testResource;
        $request->ro->uri = 'test://self/path/to/resource';

        $this->testResource['one'] = 1;
        $this->testResource['two'] = $request;
        $this->testResource->setRenderer(new JsonRenderer);
    }

    public function testRender()
    {
        // json render
        $result = (string)$this->testResource;
        $data = json_decode($result, true);
        $expected = array(
            'one' => 1,
            'two' => array(
                'code' => 200,
                'headers' => array(),
                'body' => array(
                    'one' => 1,
                    'two' => null,
                ),
                'uri' => 'test://self/path/to/resource',
                'view' => null,
                'links' => []
            ),
        );
        $this->assertSame($expected, $data);
    }
}

final class Ok extends AbstractObject
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
    public $body = '';

    /**
     * Constructor
     */
    public function __construct()
    {
    }

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

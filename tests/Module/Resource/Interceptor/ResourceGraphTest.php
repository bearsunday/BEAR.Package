<?php
namespace BEAR\Package\Module\Resource\Interceptor;

use BEAR\Package\Mock\ResourceObject\Ok;
use BEAR\Package\Module\Resource\Interceptor\ResourceGraph;
use BEAR\Resource\ResourceObject;
use BEAR\Resource\Adapter\Http;
use BEAR\Resource\SchemeCollection;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Annotation;
use Ray\Di\Config;
use Ray\Di\Container;
use Ray\Di\Definition;
use Ray\Di\Forge;
use Ray\Di\Injector;
use Doctrine\Common\Annotations\AnnotationReader;
use BEAR\Resource\Adapter\AdapterInterface;

use BEAR\Resource\Request;
use BEAR\Resource\Adapter\App;

class Foo extends ResourceObject
{
    public $body = [
        'bar'=> 'http://www.w3.org/standards/webdesign/accessibility'
    ];

    /**
     * ResourceGraph
     */
    public function onGet()
    {
        return $this;
    }
}

class DummyAdapter implements AdapterInterface
{
    public function get($uri)
    {
        return new Ok;
    }
}

class ResourceGraphTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ResourceGraph
     */
    private $resourceGraph;

    protected function setUp()
    {
        $resource = require $_ENV['PACKAGE_DIR'] . '/vendor/bear/resource/scripts/instance.php';
        /** @var $resource \BEAR\Resource\Resource */
        $scheme = new SchemeCollection;
        $scheme->scheme('http')->host('*')->toAdapter(
            new DummyAdapter
        );
        $resource->setSchemeCollection($scheme);
        $this->resourceGraph = new ResourceGraph($resource);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Module\Resource\Interceptor\ResourceGraph', $this->resourceGraph);
    }

    public function testInvoke()
    {
        $foo = new Foo;
        $invocation = new ReflectiveMethodInvocation([$foo, 'onGet'], [], [$this->resourceGraph]);
        $this->resourceGraph->invoke($invocation);
        $request = $foo->body['bar'];
        $this->assertInstanceOf('BEAR\Resource\Request', $request);

        return $request;
    }

    /**
     * @depends testInvoke
     */
    public function testRequest(Request $request)
    {
        $this->assertSame('http://www.w3.org/standards/webdesign/accessibility', $request->uri);
    }
}

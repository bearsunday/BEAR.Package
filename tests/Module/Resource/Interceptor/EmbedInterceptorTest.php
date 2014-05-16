<?php

namespace BEAR\Package\Module\Resource\Interceptor;

use BEAR\Package\Provide\ResourceView\JsonRenderer;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\Request;
use BEAR\Resource\ResourceObject;
use Doctrine\Common\Annotations\AnnotationReader;
use Ray\Aop\NamedArgs;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Injector;
use BEAR\Resource\Annotation\Link;

class EmbedMock extends ResourceObject
{

    /**
     * @Link(rel="bird1", href="app://self/canary")
     * @Link(rel="bird2", href="app://self/sparrow{?id}")
     */
    public function onGet($id)
    {
        return $this;
    }
}

class EmbedInterceptorTest extends \PHPUnit_Framework_TestCase
{

    private $embedInterceptor;

    protected function setUp()
    {
        $resource = Injector::create([new ResourceModule('Vendor\MockApp')])->getInstance('BEAR\Resource\ResourceInterface');
        $this->embedInterceptor = new EmbedInterceptor($resource, new AnnotationReader, new NamedArgs);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Module\Resource\Interceptor\EmbedInterceptor', $this->embedInterceptor);
    }

    public function testInvoke()
    {
        $mock = new EmbedMock;
        $invocation = new ReflectiveMethodInvocation([$mock, 'onGet'], ['id' => 1], [$this->embedInterceptor]);
        $result = $invocation->proceed();
        $profile = $result['bird1'];
        /** @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/canary', $profile->toUriWithMethod());

        return $result;
    }

    /**
     * @depends testInvoke
     */
    public function testInvokeAnotherLink(ResourceObject $result)
    {
        $profile = $result['bird2'];
        /** @var $profile Request */
        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
        $this->assertSame('get app://self/sparrow?id=1', $profile->toUriWithMethod());
        return $result;
    }


    /**
     * @depends testInvoke
     */
    public function testInvokeString(ResourceObject $result)
    {
        $result->setRenderer(new JsonRenderer);
        $json = (string) $result;
        $this->assertSame('{
    "bird1": {
        "name": "chill kun"
    },
    "bird2": {
        "sparrow_id": "1"
    }
}', $json);
    }
}

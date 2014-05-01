<?php

namespace BEAR\Package\Module\Cache;

use BEAR\Resource\ResourceObject;
use BEAR\Sunday\Annotation\Cache as CacheAnnotation;
use BEAR\Package\Module\Cache\Interceptor\CacheLoader;
use BEAR\Package\Mock\ResourceObject\MockResource;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\PhpFileCache;
use Guzzle\Cache\DoctrineCacheAdapter as CacheAdapter;
use Ray\Aop\ReflectiveMethodInvocation;
use Ray\Di\Definition;

class CacheLoaderTest extends \PHPUnit_Framework_TestCase
{
    const TIME = 10;

    /**
     * @var CacheAdapter
     */
    private $cacheAdapter;

    /**
     * @var CacheLoader
     */
    private $cacheLoader;

    /**
     * @var ReflectiveMethodInvocation
     */
    private $invocation;

    protected function setUp()
    {
        parent::setUp();
        $this->cacheLoader = (new CacheLoader(new ArrayCache, new AnnotationReader));
        $this->invocation = new ReflectiveMethodInvocation([new MockResource, 'onGet'], [], [$this->cacheLoader]);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Module\Cache\Interceptor\CacheLoader', $this->cacheLoader);
    }

    public function testInvokeWrite()
    {
        $result = $this->cacheLoader->invoke($this->invocation);
        $this->assertTrue(isset($result->headers['x-cache']));
    }

    public function testInvokeRead()
    {
        $this->cacheLoader->invoke($this->invocation);
        $result = $this->cacheLoader->invoke($this->invocation);
        $this->assertTrue(isset($result->headers['x-cache']));

        return $result;
    }

    /**
     * @depends testInvokeRead
     *
     * @param ResourceObject $ro
     */
    public function testInvokeReadMode(ResourceObject $result)
    {
        $cacheInfo = json_decode($result->headers['x-cache']);
        $this->assertSame('R', $cacheInfo->mode);
    }

    /**
     * @depends testInvokeRead
     *
     * @param ResourceObject $ro
     */
    public function testInvokeReadLife(ResourceObject $result)
    {
        $cacheInfo = json_decode($result->headers['x-cache']);
        $this->assertSame(self::TIME, $cacheInfo->life);
    }

    public function testInvokeWriteWithPagerQuery()
    {
        $_GET['_start'] = 1;
        $result = $this->cacheLoader->invoke($this->invocation);
        $this->assertTrue(isset($result->headers['x-cache']));
    }
    public function testInvokeWritePagerData()
    {
        $_GET['_start'] = 1;
        $result = $this->cacheLoader->invoke($this->invocation);
        unset($_GET['_start']);
        $result = $this->cacheLoader->invoke($this->invocation);
        $this->assertTrue(isset($result->headers['x-cache']));
    }

    public function testInvokeSetPagerKey()
    {
        $result = $this->cacheLoader->invoke($this->invocation);
        $this->assertTrue(isset($result->headers['x-cache']));
    }
}

<?php

namespace BEAR\Package\Module\Cache;

use BEAR\Package\Module\Cache\Interceptor\CacheUpdater;
use Ray\Aop\Arguments;
use Ray\Di\Config;
use Ray\Di\Annotation;
use Ray\Di\Definition;
use Doctrine\Common\Cache\ArrayCache;
use Ray\Aop\ReflectiveMethodInvocation;
use BEAR\Sunday\Annotation\CacheUpdate;
use Doctrine\Common\Annotations\AnnotationReader as Reader;
use BEAR\Package\Mock\ResourceObject\MockResource;

class CacheUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CacheAdapter
     */
    private $cache;

    /**
     * @var CacheUpdater
     */
    private $cacheUpdater;

    protected function setUp()
    {
        parent::setUp();
        $this->cache = new ArrayCache;
        $this->cacheUpdater = new CacheUpdater($this->cache);
    }

    public function testNew()
    {
        $this->assertInstanceOf('BEAR\Package\Module\Cache\Interceptor\CacheUpdater', $this->cacheUpdater);
    }

    public function testInvoke()
    {
        $ro = new MockResource;
        $args = [];
        $interceptors = [$this->cacheUpdater];
        $annotation = new CacheUpdate;
        $invocation = new ReflectiveMethodInvocation($ro, new \ReflectionMethod($ro, 'onPost'), new Arguments($args), $interceptors, $annotation);

        $id = $this->cacheUpdater->getEtag($ro, $args);
        $cacheData = "cache_data";
        $this->cache->save($id, $cacheData);
        $contents = $this->cache->fetch($id);
        $this->assertSame($contents, $cacheData);
        $this->cacheUpdater->invoke($invocation);
        $contents = $this->cache->fetch($id);
        $this->assertSame($contents, false);
    }
}

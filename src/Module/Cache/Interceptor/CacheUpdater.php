<?php
/**
 * This file is part of the BEAR.Package package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace BEAR\Package\Module\Cache\Interceptor;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use ReflectionMethod;
use Doctrine\Common\Cache\Cache;

class CacheUpdater implements MethodInterceptor
{
    use EtagTrait;

    /**
     * @param Cache $cache
     *
     * @Inject
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function invoke(MethodInvocation $invocation)
    {
        $ro = $invocation->getThis();

        // onGet(void) clear cache
        $id = $this->getEtag($ro, [0 => null]);
        $this->cache->delete($id);

        // onGet($id, $x, $y...) clear cache
        $getMethod = new ReflectionMethod($ro, 'onGet');
        $parameterNum = count($getMethod->getParameters());
        // cut as same size and order as onGet
        $slicedInvocationArgs = array_slice((array) $invocation->getArguments(), 0, $parameterNum);
        $id = $this->getEtag($ro, $slicedInvocationArgs);
        $this->cache->delete($id);

        return $invocation->proceed();
    }
}

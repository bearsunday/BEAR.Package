<?php

declare(strict_types=1);

namespace Ray\ProxyCache;

use Psr\Cache\CacheItemPoolInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\Di\Named;
use Ray\PsrCacheModule\Annotation\Local;
use Symfony\Contracts\Cache\CacheInterface;

use function assert;
use function call_user_func_array;
use function serialize;
use function urlencode;

/** @template T of object */
final class ProxyCacheInterceptor implements MethodInterceptor
{
    /** @param T $t */
    public function __construct(
        #[Named('original')]
        private $t,
        #[Local]
        private CacheItemPoolInterface $cache,
    ) {
    }

    /** @inheritDoc  */
    public function invoke(MethodInvocation $invocation)
    {
        $args = $invocation->getArguments()->getArrayCopy();
        $key = urlencode(static::class . $invocation->getMethod()->getName() . serialize($args));
        assert($this->cache instanceof CacheInterface);

        return $this->cache->get(
            $key,
            /** @psalm-suppress MissingClosureReturnType */
            fn () => call_user_func_array([$this->t, $invocation->getMethod()->getName()], $args)
        );
    }
}

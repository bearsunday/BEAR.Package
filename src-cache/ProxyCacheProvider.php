<?php

declare(strict_types=1);

namespace Ray\ProxyCache;

use BEAR\AppMeta\AbstractAppMeta;
use Koriym\NullObject\NullObject;
use Psr\Cache\CacheItemPoolInterface;
use Ray\Aop\Bind;
use Ray\Aop\Matcher;
use Ray\Aop\Pointcut;
use Ray\Aop\Weaver;
use Ray\Di\InjectionPointInterface;
use Ray\Di\InjectorInterface;
use Ray\Di\ProviderInterface;
use Ray\PsrCacheModule\Annotation\Local;
use ReflectionNamedType;

use function assert;

/**
 * @template T of object
 * @implements ProviderInterface<T>
 */
final class ProxyCacheProvider implements ProviderInterface
{
    public const DELEGATE = 'delegate';

    public function __construct(
        private InjectionPointInterface $ip,
        private InjectorInterface $injector,
        private AbstractAppMeta $meta,
        private NullObject $nullObject,
        #[Local]
        private CacheItemPoolInterface $cache,
    ) {
    }

    /** @psalm-suppress InvalidReturnType; */
    public function get(): object
    {
        $type = $this->ip->getParameter()->getType();
        assert($type instanceof ReflectionNamedType);
        /** @var class-string<T> $interface */
        $interface = $type->getName();
        $original = $this->injector->getInstance($interface, self::DELEGATE);
        $scriptDir = $this->meta->tmpDir . '/di';
        $pointcut = new Pointcut(
            (new Matcher())->any(),
            (new Matcher())->startsWith(''), // workaround for any() doesn't hit magic method
            [new ProxyCacheInterceptor($original, $this->cache)],
        );
        $nullClass = $this->nullObject->save($interface, $scriptDir);
        $bind = (new Bind())->bind($nullClass, [$pointcut]);

        /** @var T $cacheableProxy */
        $cacheableProxy = (new Weaver($bind, $scriptDir))->newInstance($nullClass, []);

        return $cacheableProxy;
    }
}

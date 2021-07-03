<?php

declare(strict_types=1);

namespace BEAR\Package\Context\Prod;

use BEAR\Resource\NamedParamMetasInterface;
use BEAR\Resource\ParamInterface;
use Psr\Cache\CacheItemPoolInterface;
use Ray\PsrCacheModule\Annotation\Shared;

use function assert;
use function get_class;
use function is_callable;
use function str_replace;

final class CachedNamedParamMetas implements NamedParamMetasInterface
{
    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var NamedParamMetasInterface */
    private $delegate;

    /**
     * @\Ray\PsrCacheModule\Annotation\Shared("cache")
     */
    #[Shared('cache')]
    public function __construct(CacheItemPoolInterface $cache, NamedParamMetasInterface $metas)
    {
        $this->cache = $cache;
        $this->delegate = $metas;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(callable $callable): array
    {
        /** @var array{0:object, 1:string} $callable */
        $cacheId = str_replace('\\', '_', get_class($callable[0]) . $callable[1]);
        $item = $this->cache->getItem($cacheId);
        if ($item->isHit()) {
            /** @var array<string, ParamInterface> $cached */
            $cached = $item->get();

            return $cached;
        }

        assert(is_callable($callable));
        $names = ($this->delegate)($callable);
        $item->set($names);
        $this->cache->save($item);

        return $names;
    }
}

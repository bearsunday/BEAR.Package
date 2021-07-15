<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use Ray\Di\AbstractModule;

/**
 * @deprecated
 */
class CacheNamespaceModule extends AbstractModule
{
    /** @var string */
    private $cacheNamespace;

    public function __construct(string $cacheNamespace, ?AbstractModule $module = null)
    {
        $this->cacheNamespace = $cacheNamespace;
        parent::__construct($module);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith('cache_namespace')->toInstance($this->cacheNamespace);
    }
}

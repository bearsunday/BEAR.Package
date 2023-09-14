<?php

declare(strict_types=1);

namespace Ray\ProxyCache;

use Koriym\NullObject\NullObject;
use Ray\Di\AbstractModule;
use Ray\Di\Scope;

/**
 * Provides proxy cache bindings
 */
class ProxyCacheModule extends AbstractModule
{
    public function __construct(
        private array $cacheableInterfaces,
        private AbstractModule|null $module = null,
    ) {
            parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        foreach ($this->cacheableInterfaces as $interface) {
            $this->rebind($interface, ProxyCacheProvider::DELEGATE);
            $this->bind($interface)->toProvider(ProxyCacheProvider::class)->in(Scope::class);
        }

        $this->bind(NullObject::class);
    }
}

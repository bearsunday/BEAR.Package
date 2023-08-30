<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use Ray\Compiler\ScriptInjector;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

/**
 * Provides InjectorInterface
 */
class ScriptInjectorModule extends AbstractModule
{
    public function __construct(
        private string $scriptDir,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->bind(InjectorInterface::class)->toInstance(new ScriptInjector($this->scriptDir));
    }
}

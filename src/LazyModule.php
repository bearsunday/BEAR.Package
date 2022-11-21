<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Module\ResourceObjectModule;
use BEAR\Package\Module\ScriptinjectorModule;
use Ray\Compiler\LazyModuleInterface;
use Ray\Di\AbstractModule;

class LazyModule implements LazyModuleInterface
{
    public function __construct(
        private AbstractAppMeta $appMeta,
        private string $context,
        private string $scriptDir,
    ) {
    }

    public function __invoke(): AbstractModule
    {
        $module = new ScriptinjectorModule($this->scriptDir, (new Module())($this->appMeta, $this->context));
        $module->install(new ResourceObjectModule($this->appMeta->getResourceListGenerator()));

        return $module;
    }
}

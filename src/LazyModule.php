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
    /** @var AbstractAppMeta */
    private $appMeta;

    /** @var string */
    private $context;

    /** @var string */
    private $scriptDir;

    public function __construct(AbstractAppMeta $appMeta, string $context, string $scriptDir)
    {
        $this->appMeta = $appMeta;
        $this->context = $context;
        $this->scriptDir = $scriptDir;
    }

    public function __invoke(): AbstractModule
    {
        $module = new ScriptinjectorModule($this->scriptDir, (new Module())($this->appMeta, $this->context));
        $module->install(new ResourceObjectModule($this->appMeta->getResourceListGenerator()));

        return $module;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Package\Exception\InvalidModuleException;
use Ray\Di\AbstractModule;
use Ray\Di\AssistedModule;

class Module
{
    /**
     * Return module from $appMeta and $context
     */
    public function __invoke(AbstractAppMeta $appMeta, string $context) : AbstractModule
    {
        $contextsArray = array_reverse(explode('-', $context));
        $module = new AssistedModule;
        foreach ($contextsArray as $contextItem) {
            $class = $appMeta->name . '\Module\\' . ucwords($contextItem) . 'Module';
            if (! class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($contextItem) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new InvalidContextException($contextItem);
            }
            /* @var $module AbstractModule */
            $module = is_subclass_of($class, AbstractAppModule::class) ? new $class($appMeta, $module) : new $class($module);
        }
        if (! $module instanceof AbstractModule) {
            throw new InvalidModuleException; // @codeCoverageIgnore
        }
        $module->override(new AppMetaModule($appMeta));

        return $module;
    }
}

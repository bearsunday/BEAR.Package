<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Package\Module\AppMetaModule;
use Ray\Di\AbstractModule;
use Ray\Di\AssistedModule;

use function array_reverse;
use function class_exists;
use function explode;
use function is_a;
use function is_subclass_of;
use function ucwords;

class Module
{
    /**
     * Return module from $appMeta and $context
     */
    public function __invoke(AbstractAppMeta $appMeta, string $context): AbstractModule
    {
        $contextsArray = array_reverse(explode('-', $context));
        $module = new AssistedModule();
        foreach ($contextsArray as $contextItem) {
            $module = $this->installContextModule($appMeta, $contextItem, $module);
        }

        $module->override(new AppMetaModule($appMeta));

        return $module;
    }

    private function installContextModule(AbstractAppMeta $appMeta, string $contextItem, AbstractModule $module): AbstractModule
    {
        $class = $appMeta->name . '\Module\\' . ucwords($contextItem) . 'Module';
        if (! class_exists($class)) {
            $class = 'BEAR\Package\Context\\' . ucwords($contextItem) . 'Module';
        }

        if (! is_a($class, AbstractModule::class, true)) {
            throw new InvalidContextException($contextItem);
        }

        /** @psalm-suppress UnsafeInstantiation */
        $module = is_subclass_of($class, AbstractAppModule::class) ? new $class($appMeta, $module) : new $class($module);

        return $module;
    }
}

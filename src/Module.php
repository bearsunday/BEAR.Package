<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\InvalidContextException;
use Ray\Di\AbstractModule;
use Ray\Di\NullModule;

class Module
{
    /**
     * Return module from $appMeta and $context
     */
    public function __invoke(AbstractAppMeta $appMeta, string $context) : AbstractModule
    {
        $contextsArray = array_reverse(explode('-', $context));
        $module = new NullModule;
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new InvalidContextException($context);
            }
            /* @var $module AbstractModule */
            $module = is_subclass_of($class, AbstractAppModule::class) ? new $class($appMeta, $module) : new $class($module);
        }
        if (! $module instanceof AbstractModule) {
            throw new \LogicException; // @codeCoverageIgnore
        }
        $module->override(new AppMetaModule($appMeta));

        return $module;
    }
}

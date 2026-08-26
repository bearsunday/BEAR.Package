<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Package\Module\ReadOnlyAppModule;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

/**
 * @see PackageInjector
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 */
final class Injector
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param AppName $appName
     * @param Context $context
     * @param AppDir  $appDir
     * @param null    $cache   the compiled scripts are the cache; nothing else is read
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public static function getInstance(string $appName, string $context, string $appDir, null $cache = null): InjectorInterface
    {
        return self::fromMeta(new Meta($appName, $context, $appDir), $context);
    }

    /**
     * Return an injector for an application with its own AbstractAppMeta subclass.
     *
     * Not for overriding tmpDir/logDir: the module tree overwrites them.
     *
     * @param Context $context
     *
     * @see ReadOnlyAppModule to name the write directories
     */
    public static function fromMeta(AbstractAppMeta $meta, string $context): InjectorInterface
    {
        return PackageInjector::getInstance($meta, $context);
    }

    /**
     * Return an injector with the given override module applied.
     *
     * AOP proxies and the compiled container for override injectors are stored under a
     * subdirectory of the script directory ({appDir}/var/build/{context}/di) keyed by the
     * override module class name, so they do not collide with Injector::getInstance()
     * for the same app+context.
     *
     * @param AppName $appName
     * @param Context $context
     * @param AppDir  $appDir
     *
     * @see PackageInjector::factory()
     */
    public static function getOverrideInstance(string $appName, string $context, string $appDir, AbstractModule $overrideModule): InjectorInterface
    {
        return PackageInjector::factory(new Meta($appName, $context, $appDir), $context, $overrideModule);
    }
}

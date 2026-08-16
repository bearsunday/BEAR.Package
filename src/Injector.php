<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\WriteDirRequiredException;
use BEAR\Package\Injector\PackageInjector;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;
use Ray\PsrCacheModule\LocalCacheProvider;
use Symfony\Contracts\Cache\CacheInterface;

use function preg_match;
use function str_replace;

/**
 * @see PackageInjector
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type WriteDir from Types
 */
final class Injector
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir writable base; defaults to {appDir}/var
     */
    public static function getInstance(string $appName, string $context, string $appDir, CacheInterface|null $cache = null, string|null $writeDir = null): InjectorInterface
    {
        return self::fromMeta(self::meta($appName, $context, $appDir, $writeDir), $context, $cache);
    }

    /**
     * Return an injector for an already resolved Meta.
     *
     * For an application with its own AbstractAppMeta - a bespoke resource list, say.
     * Overriding tmpDir/logDir is not a reason to come here: pass $writeDir to
     * getInstance() instead, so the build and the boot derive the same paths.
     *
     * @param Context $context
     */
    public static function fromMeta(AbstractAppMeta $meta, string $context, CacheInterface|null $cache = null): InjectorInterface
    {
        $cacheNamespace = str_replace('\\', '_', $meta->name) . $context;
        $cache ??= (new LocalCacheProvider($meta->tmpDir . '/injector', $cacheNamespace))->get();

        return PackageInjector::getInstance($meta, $context, $cache);
    }

    /**
     * Return an injector with the given override module applied.
     *
     * AOP proxies and the compiled container for override injectors are stored under a
     * subdirectory of the script directory ({appDir}/var/tmp/{context}/di) keyed by the
     * override module class name, so they do not collide with Injector::getInstance()
     * for the same app+context.
     *
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir writable base; defaults to {appDir}/var
     *
     * @see PackageInjector::factory()
     */
    public static function getOverrideInstance(string $appName, string $context, string $appDir, AbstractModule $overrideModule, string|null $writeDir = null): InjectorInterface
    {
        return PackageInjector::factory(self::meta($appName, $context, $appDir, $writeDir), $context, $overrideModule);
    }

    /**
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir
     *
     * @throws WriteDirRequiredException An application inside a stream URI has no writable var/ of its own.
     */
    private static function meta(string $appName, string $context, string $appDir, string|null $writeDir): Meta
    {
        if ($writeDir === null && preg_match('#^[A-Za-z][A-Za-z0-9+.\\-]*://#', $appDir)) {
            throw new WriteDirRequiredException($appDir);
        }

        return Meta::create($appName, $context, $appDir, $writeDir);
    }
}

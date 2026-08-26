<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\WriteDirRequiredException;
use BEAR\Package\Injector\PackageInjector;
use Ray\Di\AbstractModule;
use Ray\Di\InjectorInterface;

use function preg_match;

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
     * The $cache slot holds the position $writeDir is passed in; both go together.
     *
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param null          $cache    the compiled scripts are the cache; nothing else is read
     * @param WriteDir|null $writeDir writable base; defaults to {appDir}/var
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     */
    public static function getInstance(string $appName, string $context, string $appDir, null $cache = null, string|null $writeDir = null): InjectorInterface
    {
        return self::fromMeta(self::meta($appName, $context, $appDir, $writeDir), $context);
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

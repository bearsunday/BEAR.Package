<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use BEAR\Package\Module\ScriptinjectorModule;
use BEAR\Sunday\Extension\Application\AppInterface;
use Doctrine\Common\Cache\CacheProvider;
use Ray\Compiler\Annotation\Compile;
use Ray\Compiler\ScriptInjector;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use Ray\PsrCacheModule\LocalCacheProvider;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;

use function assert;
use function is_bool;
use function is_dir;
use function mkdir;
use function str_replace;

final class Injector
{
    /**
     * Serialized injector instances
     *
     * @var array<string, InjectorInterface>
     */
    private static $instances;

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    public static function getInstance(string $appName, string $context, string $appDir, ?CacheProvider $cache = null): InjectorInterface
    {
        $injectorId = str_replace('\\', '_', $appName) . $context;
        if (isset(self::$instances[$injectorId])) {
            return self::$instances[$injectorId];
        }

        $meta = new Meta($appName, $context, $appDir);
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $cache = $cache ? new DoctrineAdapter($cache, $injectorId) : (new LocalCacheProvider($meta->tmpDir . '/cache', $injectorId))->get();
        /** @var InjectorInterface $injector */
        $injector = $cache->get($injectorId, static function () use ($meta, $context): InjectorInterface {
            $injector = self::factory($meta, $context);
            $injector->getInstance(AppInterface::class);

            return $injector;
        });
        self::$instances[$injectorId] = $injector;

        return $injector;
    }

    private static function factory(Meta $meta, string $context): InjectorInterface
    {
        $scriptDir = $meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! @mkdir($scriptDir) && ! is_dir($scriptDir);
        $module = (new Module())($meta, $context, '');
        $rayInjector = new RayInjector($module, $scriptDir);
        $isProd = $rayInjector->getInstance('', Compile::class);
        assert(is_bool($isProd));
        if ($isProd) {
            return new ScriptInjector($scriptDir, static function () use ($scriptDir, $module) {
                return new ScriptinjectorModule($scriptDir, $module);
            });
        }

        return $rayInjector;
    }
}

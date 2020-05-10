<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\Meta;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Compiler\ScriptInjector as RayScriptJinjector;
use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\InjectorInterface;

final class ScriptInjector implements InjectorInterface
{
    /**
     * @var FilesystemCache
     */
    private $cache;

    /**
     * @var string
     */
    private $context;

    /**
     * @var Meta
     */
    private $meta;

    /**
     * @var AppInjector
     */
    private $injector;

    public function __construct(string $name, string $context, string $appDir, string $cacheNamespace = '')
    {
        $this->meta = new Meta($name, $context, $appDir);
        $appCacheDir = $this->meta->tmpDir . '/app';
        ! is_dir($appCacheDir) && ! mkdir($appCacheDir) && ! is_dir($appCacheDir) && touch($appCacheDir . '/.do_not_clear');
        $scriptDir = $this->meta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! mkdir($scriptDir) && ! is_dir($scriptDir);
        $cacheDir = $this->meta->tmpDir . '/cache';
        ! is_dir($cacheDir) && ! mkdir($cacheDir) && ! is_dir($cacheDir);
        $cache = new FilesystemCache($cacheDir);
        $cache->setNamespace($name . $context . $cacheNamespace);
        $this->injector = new RayScriptJinjector($scriptDir, function () use ($cacheNamespace, $context) : AbstractModule {
            $module = (new Module)($this->meta, $context);
            $container = $module->getContainer();
            (new Bind($container, InjectorInterface::class))->toInstance($this->injector);
            (new Bind($container, ''))->annotatedWith('cache_namespace')->toInstance($cacheNamespace);

            return $module;
        });
        $this->cache = $this->injector->getInstance(Cache::class);
        $this->context = $context;
    }

    /**
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = '')
    {
        $id = $interface . $name;
        $instance = $this->cache->fetch($id);
        if ($instance) {
            return $instance;
        }
        $instance = $this->injector->getInstance($interface, $name);
        $this->cache->save($id, $instance);
        // Injected $injector (like in Resource\Factory) compile the class each time for the development.
        $this->injector->disableCache();

        return $instance;
    }
}

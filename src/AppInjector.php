<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use Doctrine\Common\Cache\FilesystemCache;
use Ray\Compiler\ScriptInjector;
use Ray\Di\AbstractModule;
use Ray\Di\Bind;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class AppInjector implements InjectorInterface
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @var string
     */
    private $appDir;

    /**
     * @var ScriptInjector
     */
    private $injector;

    /**
     * @var string
     */
    private $cacheNamespace;

    /**
     * @var null|AbstractModule
     */
    private $module;

    public function __construct(string $name, string $context, AbstractAppMeta $appMeta = null, string $cacheNamespace = null)
    {
        $this->context = $context;
        $this->appMeta = $appMeta instanceof AbstractAppMeta ? $appMeta : new Meta($name, $context);
        $this->cacheNamespace = (string) $cacheNamespace;
        $scriptDir = $this->appMeta->tmpDir . '/di';
        ! is_dir($scriptDir) && ! mkdir($scriptDir) && ! is_dir($scriptDir);
        $this->scriptDir = $scriptDir;
        $appDir = $this->appMeta->tmpDir . '/app';
        ! is_dir($appDir) && ! mkdir($appDir) && ! is_dir($appDir);
        touch($appDir . '/.do_not_clear');
        $this->appDir = $appDir;
        $this->injector = new ScriptInjector($this->scriptDir, function () {
            return $this->getModule();
        });
        if ($cacheNamespace === null) {
            $this->clear();
        }
    }

    /**
     * {inheritdoc}
     *
     * @param string $interface
     * @param string $name
     *
     * @return mixed
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        return $this->injector->getInstance($interface, $name);
    }

    /**
     * @return mixed
     */
    public function getOverrideInstance(AbstractModule $module, string $interface, string $name = Name::ANY)
    {
        $appModule = clone $this->getModule();
        $appModule->override($module);

        return (new Injector($appModule, $this->scriptDir))->getInstance($interface, $name);
    }

    public function clear() : void
    {
        if ((new Unlink)->once($this->appMeta->tmpDir)) {
            return;
        }
        $diDir = $this->appMeta->tmpDir . '/di';
        ! is_dir($diDir) && ! mkdir($diDir) && ! is_dir($diDir);
        file_put_contents($this->scriptDir . ScriptInjector::MODULE, serialize($this->getModule()));
    }

    /**
     * @return mixed
     */
    public function getCachedInstance(string $interface, string $name = Name::ANY)
    {
        $cache = new FilesystemCache($this->appDir);
        $id = $interface . $name . $this->context . $this->cacheNamespace;
        $instance = $cache->fetch($id);
        if ($instance) {
            return $instance;
        }
        $instance = $this->injector->getInstance($interface, $name);
        $cache->save($id, $instance);

        return $instance;
    }

    private function getModule() : AbstractModule
    {
        if ($this->module instanceof AbstractModule) {
            return $this->module;
        }
        $module = (new Module)($this->appMeta, $this->context);
        /* @var AbstractModule $module */
        $container = $module->getContainer();
        (new Bind($container, InjectorInterface::class))->toInstance($this->injector);
        (new Bind($container, ''))->annotatedWith('cache_namespace')->toInstance($this->cacheNamespace);
        $this->module = $module;

        return $module;
    }
}

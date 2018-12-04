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
    private $cacheSpace;

    /**
     * @var null|AbstractModule
     */
    private $module;

    public function __construct(string $name, string $context, AbstractAppMeta $appMeta = null, string $cacheSpace = '')
    {
        $this->context = $context;
        $this->appMeta = $appMeta instanceof AbstractAppMeta ? $appMeta : new Meta($name, $context);
        $this->cacheSpace = $cacheSpace;
        $scriptDir = $this->appMeta->tmpDir . '/di';
        ! \file_exists($scriptDir) && \mkdir($scriptDir);
        $this->scriptDir = $scriptDir;
        $appDir = $this->appMeta->tmpDir . '/app';
        ! \file_exists($appDir) && \mkdir($appDir);
        touch($appDir . '/.do_not_clear');
        $this->appDir = $appDir;
        $this->injector = new ScriptInjector($this->scriptDir, function () {
            return $this->getModule();
        });
        if (! $cacheSpace) {
            $this->clear();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        return $this->injector->getInstance($interface, $name);
    }

    public function getOverrideInstance(AbstractModule $module, $interface, $name = Name::ANY)
    {
        $appModule = clone $this->getModule();
        $appModule->override($module);

        return (new Injector($appModule, $this->scriptDir))->getInstance($interface, $name);
    }

    public function clear()
    {
        if ((new Unlink)->once($this->appMeta->tmpDir)) {
            return;
        }
        ! is_dir($this->appMeta->tmpDir . '/di') && \mkdir($this->appMeta->tmpDir . '/di');
        file_put_contents($this->scriptDir . ScriptInjector::MODULE, serialize($this->getModule()));
    }

    public function getCachedInstance($interface, $name = Name::ANY)
    {
        $lockFile = $this->appMeta->appDir . '/composer.lock';
        $this->cacheSpace .= file_exists($lockFile) ? (string) filemtime($lockFile) : '';
        $cache = new FilesystemCache($this->appDir);
        $id = $interface . $name . $this->context . $this->cacheSpace;
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
        (new Bind($container, ''))->annotatedWith('cache_namespace')->toInstance($this->cacheSpace);
        $this->module = $module;

        return $module;
    }
}

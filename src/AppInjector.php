<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
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
     * @var ScriptInjector
     */
    private $injector;

    /**
     * @var string
     */
    private $cacheSpace;

    public function __construct(string $name, string $context, AbstractAppMeta $appMeta = null, string $cacheSpace = '', callable $init = null)
    {
        $this->context = $context;
        $this->appMeta = $appMeta instanceof AbstractAppMeta ? $appMeta : new Meta($name, $context);
        $this->cacheSpace = $cacheSpace;
        $scriptDir = $this->appMeta->tmpDir . '/di';
        ! \file_exists($scriptDir) && \mkdir($scriptDir);
        $this->scriptDir = $scriptDir;
        $this->injector = new ScriptInjector($this->scriptDir, function () {
            return $this->getModule();
        });
        is_callable($init) ? $init() : $this->clear();
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
        file_put_contents($this->scriptDir . ScriptInjector::MODULE, $this->getModule());
    }

    private function getModule() : AbstractModule
    {
        $module = (new Module)($this->appMeta, $this->context);
        /* @var AbstractModule $module */
        $container = $module->getContainer();
        (new Bind($container, InjectorInterface::class))->toInstance($this->injector);
        (new Bind($container, ''))->annotatedWith('cache_namespace')->toInstance($this->cacheSpace);

        return $module;
    }
}

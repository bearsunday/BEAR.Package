<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\AppMeta;
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
     * @var array
     */
    private static $clearDirs = [];

    public function __construct(string $name, string $context, AbstractAppMeta $appMeta = null)
    {
        $this->context = $context;
        $this->appMeta = $appMeta instanceof AbstractAppMeta ? $appMeta : new AppMeta($name, $context);
        $scriptDir = $this->appMeta->tmpDir . '/di';
        ! \file_exists($scriptDir) && \mkdir($scriptDir);
        $this->scriptDir = $scriptDir;
        $this->injector = new ScriptInjector($this->scriptDir, function () {
            return $this->getModule();
        });
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
        $doNotClear = in_array($this->scriptDir, self::$clearDirs, true) || file_exists($this->appMeta->tmpDir . '/.do_not_clear');
        if ($doNotClear) {
            return;
        }
        self::$clearDirs[] = $this->scriptDir;
        $this->injector->clear();
        file_put_contents($this->scriptDir . ScriptInjector::MODULE, $this->getModule());
    }

    private function getModule() : AbstractModule
    {
        $module = (new Module)($this->appMeta, $this->context);
        /* @var AbstractModule $module */
        $container = $module->getContainer();
        (new Bind($container, InjectorInterface::class))->toInstance($this->injector);

        return $module;
    }
}

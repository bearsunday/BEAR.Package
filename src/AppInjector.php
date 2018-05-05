<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\AppMeta;
use BEAR\Package\Exception\InvalidContextException;
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

    private function getModule() : AbstractModule
    {
        $contextsArray = array_reverse(explode('-', $this->context));
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $this->appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new InvalidContextException($class);
            }
            /* @var $module AbstractModule */
            $module = new $class($module);
        }
        $module->override(new AppMetaModule($this->appMeta));
        (new Bind($module->getContainer(), InjectorInterface::class))->toInstance($this->injector);

        return $module;
    }
}

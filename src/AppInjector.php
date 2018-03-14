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
use BEAR\Package\Provide\Resource\ResourceObjectModule;
use Ray\Compiler\DiCompiler;
use Ray\Compiler\Exception\NotCompiled;
use Ray\Compiler\ScriptInjector;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class AppInjector implements InjectorInterface
{
    /**
     * @var AbstractModule
     */
    private $appModule;

    /**
     * @var string
     */
    private $scriptDir;

    /**
     * @var string
     */
    private $logDir;

    public function __construct($name, $contexts)
    {
        $appMeta = new AppMeta($name, $contexts);
        $this->scriptDir = $appMeta->tmpDir;
        $this->logDir = $appMeta->logDir;
        $this->appModule = $this->newModule($appMeta, $contexts);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            return $this->getInjector()->getInstance($interface, $name);
        } catch (NotCompiled $e) {
            file_put_contents(sprintf('%s/%s', $this->logDir, 'compile-err.log'), (string) $e);

            throw $e;
        }
    }

    public function getOverrideInstance(AbstractModule $module, $interface, $name = Name::ANY)
    {
        $appModule = clone $this->appModule;
        $appModule->override($module);

        return (new Injector($appModule, $this->scriptDir))->getInstance($interface, $name);
    }

    private function getInjector() : InjectorInterface
    {
        $scriptInjector = new ScriptInjector($this->scriptDir);
        try {
            $injector = $scriptInjector->getInstance(InjectorInterface::class);
        } catch (NotCompiled $e) {
            $this->compile($this->appModule, $this->scriptDir);
            $injector = $scriptInjector->getInstance(InjectorInterface::class);
        }

        return $injector;
    }

    private function compile(AbstractModule $module, string $scriptDir)
    {
        $compiler = new DiCompiler($module, $scriptDir);
        $compiler->compile();
    }

    /**
     * Return configured module
     */
    private function newModule(AbstractAppMeta $appMeta, string $contexts) : AbstractModule
    {
        $contextsArray = array_reverse(explode('-', $contexts));
        $module = null;
        foreach ($contextsArray as $context) {
            $class = $appMeta->name . '\Module\\' . ucwords($context) . 'Module';
            if (! class_exists($class)) {
                $class = 'BEAR\Package\Context\\' . ucwords($context) . 'Module';
            }
            if (! is_a($class, AbstractModule::class, true)) {
                throw new InvalidContextException($class);
            }
            /* @var $module AbstractModule */
            $module = new $class($module);
        }
        $module->install(new ResourceObjectModule($appMeta));
        $module->override(new AppMetaModule($appMeta));

        return $module;
    }
}

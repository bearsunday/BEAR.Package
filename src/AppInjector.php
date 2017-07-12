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
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class AppInjector implements InjectorInterface
{
    /**
     * @var InjectorInterface
     */
    private $injector;

    /**
     * @var AppMeta
     */
    private $appMeta;

    public function __construct($name, $contexts)
    {
        $this->appMeta = new AppMeta($name, $contexts);
        $this->injector = $this->getInjector($this->appMeta, $contexts);
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        try {
            return $this->injector->getInstance($interface, $name);
        } catch (NotCompiled $e) {
            file_put_contents(sprintf('%s/%s', $this->appMeta->logDir, 'compile-err.log'), (string) $e);

            throw $e;
        }
    }

    /**
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return InjectorInterface
     */
    private function getInjector(AbstractAppMeta $appMeta, $contexts)
    {
        $module = $this->newModule($appMeta, $contexts);
        $module->override(new AppMetaModule($appMeta));
        $scriptDir = $appMeta->tmpDir;
        $scriptInjector = new ScriptInjector($scriptDir);
        try {
            $injector = $scriptInjector->getInstance(InjectorInterface::class);
        } catch (NotCompiled $e) {
            $this->compile($module, $appMeta, $scriptDir);
            $injector = $scriptInjector->getInstance(InjectorInterface::class);
        }

        return $injector;
    }

    /**
     * Compile dependencies
     *
     * @param AbstractModule  $module
     * @param AbstractAppMeta $appMeta
     * @param string          $scriptDir
     */
    private function compile(AbstractModule $module, AbstractAppMeta $appMeta, $scriptDir)
    {
        $compiler = new DiCompiler($module, $scriptDir);
        $compiler->compile();
    }

    /**
     * Return configured module
     *
     * @param AbstractAppMeta $appMeta
     * @param string          $contexts
     *
     * @return AbstractModule
     */
    private function newModule(AbstractAppMeta $appMeta, $contexts)
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

        return $module;
    }
}

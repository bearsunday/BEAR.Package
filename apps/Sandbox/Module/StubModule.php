<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use Ray\Di\AbstractModule;
use BEAR\Package\Module\Stub\StubModule as PackageStubModule;

/**
 * Stub module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class StubModule extends AbstractModule
{
    /**
     * (non-PHPdoc)
     * @see Ray\Di.AbstractModule::configure()
     */
    protected function configure()
    {
        $this->install(new DevModule);
        $stub = include __DIR__ . '/config/stub/resource.php';
        /** @var $stub array */
        $this->install(new PackageStubModule($stub));
    }
}

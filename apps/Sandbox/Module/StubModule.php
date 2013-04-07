<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Package\Module\Stub\StubModule as PackageStubModule;
use Ray\Di\AbstractModule;

/**
 * Stub module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class StubModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new DevModule);
        $stub = include __DIR__ . '/config/stub/resource.php';
        /** @var $stub array */
        $this->install(new PackageStubModule($stub));
    }
}

<?php
/**
 * @package    Sandbox
 * @subpackage Module
 */
namespace Sandbox\Module;

use BEAR\Package\Module as PackageModule;
use BEAR\Sunday\Module;
use BEAR\Sunday\Module\Constant;
use Sandbox\Module\ProdModule;

/**
 * Production module
 *
 * @package    Sandbox
 * @subpackage Module
 */
class TestModule extends ProdModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new App\AppModule('test'));
        $this->install(new PackageModule\Resource\NullCacheModule($this));
    }
}

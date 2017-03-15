<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package\Provide\Resource;

use BEAR\AppMeta\AbstractAppMeta;
use Ray\Di\AbstractModule;

class ResourceObjectModule extends AbstractModule
{
    /**
     * @var AbstractAppMeta
     */
    private $appMeta;

    public function __construct(AbstractAppMeta $appMeta, AbstractModule $module = null)
    {
        $this->appMeta = $appMeta;
        parent::__construct($module);
    }

    protected function configure()
    {
        $gen = $this->appMeta->getResourceListGenerator();
        foreach ($gen as list($class)) {
            $this->bind($class);
        }
    }
}

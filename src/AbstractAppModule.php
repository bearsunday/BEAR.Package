<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use Ray\Di\AbstractModule;

abstract class AbstractAppModule extends AbstractModule
{
    /**
     * @var AbstractAppMeta
     */
    protected $appMeta;

    final public function __construct(AbstractAppMeta $appMeta, AbstractModule $module)
    {
        $this->appMeta = $appMeta;
        parent::__construct($module);
    }
}

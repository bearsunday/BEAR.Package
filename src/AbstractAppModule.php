<?php

declare(strict_types=1);

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

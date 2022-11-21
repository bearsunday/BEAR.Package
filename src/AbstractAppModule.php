<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use Ray\Di\AbstractModule;

abstract class AbstractAppModule extends AbstractModule
{
    final public function __construct(
        protected AbstractAppMeta $appMeta,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }
}

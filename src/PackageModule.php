<?php
/**
 * This file is part of the BEAR.Package package.
 *
 * @license http://opensource.org/licenses/MIT MIT
 */
namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\VndErrorModule;
use BEAR\Package\Provide\Router\WebRouterModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use BEAR\Sunday\Module\SundayModule;
use Ray\Di\AbstractModule;

class PackageModule extends AbstractModule
{
    public function __construct(AbstractAppMeta $appMeta = null)
    {
        unset($appMeta); // for BC
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->install(new QueryRepositoryModule);
        $this->install(new WebRouterModule);
        $this->install(new VndErrorModule);
        $this->install(new SundayModule);
    }
}

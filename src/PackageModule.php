<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Provide\Error\VndErrorModule;
use BEAR\Package\Provide\Logger\PsrLoggerModule;
use BEAR\Package\Provide\Representation\CreatedResourceModule;
use BEAR\Package\Provide\Router\WebRouterModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use BEAR\Streamer\StreamModule;
use BEAR\Sunday\Module\SundayModule;
use Ray\Di\AbstractModule;

class PackageModule extends AbstractModule
{
    protected $appMeta;

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
        $this->install(new PsrLoggerModule);
        $this->install(new StreamModule);
        $this->install(new CreatedResourceModule);
        $this->install(new SundayModule);
    }
}

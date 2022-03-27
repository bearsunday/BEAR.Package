<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Module\Psr6NullModule;
use BEAR\Package\Provide\Error\VndErrorModule;
use BEAR\Package\Provide\Logger\PsrLoggerModule;
use BEAR\Package\Provide\Representation\CreatedResourceModule;
use BEAR\Package\Provide\Router\WebRouterModule;
use BEAR\QueryRepository\QueryRepositoryModule;
use BEAR\Streamer\StreamModule;
use BEAR\Sunday\Module\SundayModule;
use Ray\Compiler\DiCompileModule;
use Ray\Di\AbstractModule;

/**
 * Provides framework base bindings
 */
class PackageModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->install(new QueryRepositoryModule());
        $this->override(new Psr6NullModule());
        $this->install(new WebRouterModule());
        $this->install(new VndErrorModule());
        $this->install(new PsrLoggerModule());
        $this->install(new StreamModule());
        $this->install(new CreatedResourceModule());
        $this->install(new DiCompileModule(false));
        $this->install(new SundayModule());
    }
}

<?php

declare(strict_types=1);

namespace Import\HelloWorld\Module;

use BEAR\Resource\Annotation\AppName;
use BEAR\Sunday\Extension\Application\AppInterface;
use BEAR\Sunday\Module\SundayModule;
use Ray\Compiler\Annotation\Compile;
use Ray\Di\AbstractModule;

class AppModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(AppInterface::class)->to(App::class);
        $this->bind()->annotatedWith(Compile::class)->toInstance(true);
        $this->install(new SundayModule());
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Context;

use BEAR\Resource\Annotation\ContextScheme;
use BEAR\Sunday\Annotation\DefaultSchemeHost;
use Ray\Di\AbstractModule;

class ApiModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->bind()->annotatedWith(DefaultSchemeHost::class)->toInstance('app://self');
        $this->bind()->annotatedWith(ContextScheme::class)->toInstance('app://self');
    }
}

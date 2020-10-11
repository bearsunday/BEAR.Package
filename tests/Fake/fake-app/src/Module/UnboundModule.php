<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use FakeVendor\HelloWorld\UnboundConsumer;
use Ray\Di\AbstractModule;

class UnboundModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(UnboundConsumer::class);
    }
}

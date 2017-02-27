<?php

namespace FakeVendor\HelloWorld\Module;

use BEAR\Sunday\Extension\Application\AbstractApp;
use FakeVendor\HelloWorld\FakeApp;
use Ray\Di\AbstractModule;

class TestModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->bind(AbstractApp::class)->to(FakeApp::class);
    }
}

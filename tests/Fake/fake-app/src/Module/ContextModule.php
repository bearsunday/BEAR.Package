<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use FakeVendor\HelloWorld\Module\Provider\ContextlProvider;
use Ray\Di\AbstractModule;

class ContextModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('usr_db')->toProvider(ContextlProvider::class, 'user');
        $this->bind()->annotatedWith('job_db')->toProvider(ContextlProvider::class, 'job');
    }
}

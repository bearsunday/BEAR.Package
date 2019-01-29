<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Package\AbstractAppModule;
use FakeVendor\HelloWorld\Module\Provider\ContextlProvider;

class ContextModule extends AbstractAppModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('usr_db')->toProvider(ContextlProvider::class, 'user');
        $this->bind()->annotatedWith('job_db')->toProvider(ContextlProvider::class, 'job');
    }
}

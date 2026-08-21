<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Sunday\Compile\CompileStepInterface;
use FakeVendor\HelloWorld\FakeFailingStep;
use Ray\Di\AbstractModule;
use Ray\Di\MultiBinder;

/** One throwing step, for the "*-failstep-*" contexts */
class FailstepModule extends AbstractModule
{
    protected function configure(): void
    {
        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding('failing')->to(FakeFailingStep::class);
    }
}

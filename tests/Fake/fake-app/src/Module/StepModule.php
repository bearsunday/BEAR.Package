<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Sunday\Compile\CompileStepInterface;
use FakeVendor\HelloWorld\FakeAlphaStep;
use FakeVendor\HelloWorld\FakeBetaStep;
use Ray\Di\AbstractModule;
use Ray\Di\MultiBinder;

/** Two steps under different keys, for the "*-step-*" contexts */
class StepModule extends AbstractModule
{
    protected function configure(): void
    {
        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding('alpha')->to(FakeAlphaStep::class);
        MultiBinder::newInstance($this, CompileStepInterface::class)
            ->addBinding('beta')->to(FakeBetaStep::class);
    }
}

<?php

declare(strict_types=1);

namespace FakeVendor\HelloWorld\Module;

use BEAR\Sunday\Extension\Error\ThrowableHandlerInterface;
use BEAR\Sunday\Extension\Router\RouterMatch;
use Ray\Di\AbstractModule;
use Throwable;

class BadModule extends AbstractModule
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        // Invalid. You can not to bind unserializable object.
        $this->bind(ThrowableHandlerInterface::class)->toInstance(new class implements ThrowableHandlerInterface {
            public function handle(Throwable $e, RouterMatch $request): ThrowableHandlerInterface
            {
            }
            public function transfer(): void
            {
            }
        });
    }
}

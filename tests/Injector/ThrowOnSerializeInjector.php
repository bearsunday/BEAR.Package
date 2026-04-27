<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use Exception;
use Ray\Di\InjectorInterface;
use Ray\Di\Name;

final class ThrowOnSerializeInjector implements InjectorInterface
{
    public function getInstance($interface, $name = Name::ANY)
    {
        return null;
    }

    public function __serialize(): array
    {
        throw new Exception('serialize failed');
    }
}

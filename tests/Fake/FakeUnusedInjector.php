<?php

declare(strict_types=1);

namespace BEAR\Package;

use Ray\Di\InjectorInterface;
use Ray\Di\Name;

/**
 * Injector for collaborators that require one but never resolve through it.
 *
 * Resolving is a test bug, not a valid path, so it throws instead of returning null.
 */
final class FakeUnusedInjector implements InjectorInterface
{
    /**
     * @param ''|class-string $interface
     * @param string          $name
     *
     * @return never
     */
    public function getInstance($interface, $name = Name::ANY)
    {
        throw new UnexpectedInjectionException($interface);
    }
}

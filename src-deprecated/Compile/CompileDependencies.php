<?php

declare(strict_types=1);

namespace BEAR\Package\Compile;

use BEAR\Package\Compiler\NewInstance;
use Ray\Di\AbstractModule;
use function array_keys;
use function assert;
use function is_int;
use function sort;
use function strpos;
use function substr;

/** @deprecated Use CompiledInjector */
final class CompileDependencies
{
    public function __construct(
        private NewInstance $newInstance,
    ) {
    }

    public function __invoke(AbstractModule $module): AbstractModule
    {
        $container = $module->getContainer()->getContainer();
        $dependencies = array_keys($container);
        sort($dependencies);
        foreach ($dependencies as $dependencyIndex) {
            $pos = strpos((string) $dependencyIndex, '-');
            assert(is_int($pos));
            /** @var ''|class-string $interface */
            $interface = substr((string) $dependencyIndex, 0, $pos);
            $name = substr((string) $dependencyIndex, $pos + 1);
            ($this->newInstance)($interface, $name);
        }

        return $module;
    }
}

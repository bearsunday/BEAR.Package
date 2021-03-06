<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use Ray\Di\AbstractModule;

use function array_keys;
use function assert;
use function is_int;
use function sort;
use function strpos;
use function substr;

final class CompileDependencies
{
    /** @var NewInstance */
    private $newInstance;

    public function __construct(NewInstance $newInstance)
    {
        $this->newInstance = $newInstance;
    }

    public function __invoke(AbstractModule $module): AbstractModule
    {
        $container = $module->getContainer()->getContainer();
        $dependencies = array_keys($container);
        sort($dependencies);
        foreach ($dependencies as $dependencyIndex) {
            $pos = strpos((string) $dependencyIndex, '-');
            assert(is_int($pos));
            $interface = substr((string) $dependencyIndex, 0, $pos);
            $name = substr((string) $dependencyIndex, $pos + 1);
            ($this->newInstance)($interface, $name);
        }

        return $module;
    }
}

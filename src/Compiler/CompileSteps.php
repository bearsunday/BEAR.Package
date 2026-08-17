<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\DirectoryNotWritableException;
use BEAR\Package\Injector\CompiledScripts;
use BEAR\Package\Types;
use BEAR\Sunday\Compile\CompileStepInterface;
use Ray\Di\Di\Set;
use Ray\Di\InjectorInterface;
use Ray\Di\MultiBinding\Map;

use function assert;
use function is_dir;
use function mkdir;

/**
 * Runs the compile steps modules contributed, each in the build sub directory its binding key names.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type StepCounts from Types
 */
final class CompileSteps
{
    /** @param Map<CompileStepInterface> $steps */
    public function __construct(
        #[Set(CompileStepInterface::class)]
        private readonly Map $steps,
    ) {
    }

    /**
     * @param AppDir $appDir
     *
     * @return StepCounts
     *
     * @throws DirectoryNotWritableException
     */
    public static function run(InjectorInterface $injector, string $appDir): array
    {
        return $injector->getInstance(self::class)(CompiledScripts::buildDir($appDir));
    }

    /**
     * @param BuildDir $buildDir
     *
     * @return StepCounts artifacts written, keyed by binding key
     *
     * @throws DirectoryNotWritableException
     */
    public function __invoke(string $buildDir): array
    {
        $counts = [];
        foreach ($this->steps as $key => $step) {
            assert($step instanceof CompileStepInterface);
            $stepDir = $buildDir . '/' . $key;
            // Created here, not by the step: the same step object is injectable while serving.
            if (! is_dir($stepDir) && ! @mkdir($stepDir, 0777, true) && ! is_dir($stepDir)) {
                throw new DirectoryNotWritableException($stepDir);
            }

            $counts[(string) $key] = $step($stepDir);
        }

        return $counts;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\DirectoryNotWritableException;
use BEAR\Package\Injector\CompiledScripts;
use BEAR\Package\Types;
use BEAR\Sunday\Compile\CompileStepInterface;
use FilesystemIterator;
use Ray\Di\Di\Set;
use Ray\Di\InjectorInterface;
use Ray\Di\MultiBinding\Map;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function assert;
use function is_dir;
use function mkdir;
use function rmdir;
use function unlink;

/**
 * Runs the compile steps modules contributed, each in an empty build sub directory its binding key names.
 *
 * @psalm-import-type AppDir from Types
 * @psalm-import-type BuildDir from Types
 * @psalm-import-type Context from Types
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
     * @param AppDir  $appDir
     * @param Context $context the one being compiled; its build directory is the steps' own
     *
     * @return StepCounts
     *
     * @throws DirectoryNotWritableException
     */
    public static function run(InjectorInterface $injector, string $appDir, string $context): array
    {
        return $injector->getInstance(self::class)(CompiledScripts::buildDir($appDir, $context));
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
            $this->reset($stepDir);
            $counts[(string) $key] = $step($stepDir);
        }

        return $counts;
    }

    /**
     * Hand the step an empty directory it did not have to create.
     *
     * The step object is injectable while serving, so it cannot own this; and a step handed
     * the last build's files writes a different set than the same sources would from empty.
     *
     * @throws DirectoryNotWritableException
     */
    private function reset(string $stepDir): void
    {
        if (! is_dir($stepDir) && ! @mkdir($stepDir, 0777, true) && ! is_dir($stepDir)) {
            throw new DirectoryNotWritableException($stepDir);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($stepDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            if ($file->isDir()) {
                rmdir($pathname);
                continue;
            }

            unlink($pathname);
        }
    }
}

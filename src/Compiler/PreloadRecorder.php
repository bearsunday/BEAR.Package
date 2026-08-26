<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Package\Types;
use Ray\Compiler\ScriptInjectorInterface;

use function is_file;
use function ob_end_clean;
use function ob_start;

/**
 * Write preload.php from what a boot loads.
 *
 * The compile process is the wrong place to measure: it loads the module tree, Ray.Di's
 * assembler and Ray.Compiler, and it has already loaded the boot path itself before the
 * tracker starts. This runs in a process that does nothing but boot the application the
 * way production does - from the compiled scripts - so the recorded classes are the ones
 * a request actually pulls in.
 *
 * @psalm-import-type AppName from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type Context from Types
 */
final class PreloadRecorder
{
    /**
     * @param AppName $appName
     * @param Context $context
     * @param AppDir  $appDir
     *
     * @return non-empty-string the generated preload.php
     *
     * @throws PreloadRecordException
     */
    public function __invoke(string $appName, string $context, string $appDir): string
    {
        $tracker = ClassTracker::fromAppDir($appDir);
        $tracker->register();
        // Same stubs the parent compile honours: .compile.php replaces the providers that
        // cannot run on a build machine. Without it this boot would run the real ones - the
        // recording would write to databases and send mail. Stub classes drop out of the
        // list anyway, their declaring file does not match the Composer map (#486).
        $compileStub = $appDir . '/.compile.php';
        if (is_file($compileStub)) {
            require $compileStub;
        }

        $meta = new Meta($appName, $context, $appDir);
        $scriptDir = $meta->buildDir . '/di';
        // Without a current marker the boot below compiles on demand and the recording
        // measures that compile - the very error this pipeline removes.
        if (! CompileMarker::matches($scriptDir, $meta->name, $context)) {
            throw PreloadRecordException::scriptsNotCurrent($scriptDir, $context);
        }

        // factory(), not the cached facade: recording must not warm the runtime cache
        // the deployed artifact starts with, and this is the boot a cold start runs.
        $injector = PackageInjector::factory($meta, $context);
        // The marker proves the scripts are current, not that this context reads them: only a
        // compiled context does. Recording a context that assembles per request would write
        // the assembler into preload.php - what this pipeline exists to stop.
        if (! $injector instanceof ScriptInjectorInterface) {
            throw PreloadRecordException::notCompiled($context);
        }

        /** @var ArrayObject<int, string> $overwritten */
        $overwritten = new ArrayObject();
        $filePutContents = new FilePutContents($overwritten);
        $fakeRun = new FakeRun($injector, $context, $meta, transfersResponse: true);
        $dumpAutoload = new CompileAutoload($fakeRun, $filePutContents, $tracker->classes(), $appDir, $context);
        $compilePreload = new CompilePreload(
            $fakeRun,
            $dumpAutoload,
            $filePutContents,
            $tracker->classes(),
            $tracker->filter(),
        );

        // Buffer the boot: recording is not the caller's output. A fatal still surfaces - PHP
        // flushes buffers on shutdown, and failures here are reported through STDERR.
        ob_start();
        try {
            return ($compilePreload)($meta, $context);
        } finally {
            ob_end_clean();
        }
    }
}

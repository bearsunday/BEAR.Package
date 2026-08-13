<?php

declare(strict_types=1);

namespace BEAR\Package;

use ArrayObject;
use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Compiler\ClassTracker;
use BEAR\Package\Compiler\CompileAutoload;
use BEAR\Package\Compiler\CompileClassMetaInfo;
use BEAR\Package\Compiler\CompileObjectGraph;
use BEAR\Package\Compiler\FakeRun;
use BEAR\Package\Compiler\FilePutContents;
use BEAR\Package\Exception\DelegatedCompileException;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Exception\WriteDirMismatchException;
use BEAR\Package\Injector\AppDirs;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Resource\NamedParameterInterface;
use FilesystemIterator;
use Ray\Di\InjectorInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use RuntimeException;
use SplFileInfo;

use function assert;
use function dirname;
use function escapeshellarg;
use function file_exists;
use function is_dir;
use function is_executable;
use function is_float;
use function memory_get_peak_usage;
use function microtime;
use function mkdir;
use function number_format;
use function passthru;
use function printf;
use function realpath;
use function rmdir;
use function sprintf;
use function unlink;

use const PHP_BINARY;
use const PHP_BINDIR;
use const PHP_EOL;
use const PHP_SAPI;

/**
 * @psalm-import-type AppName from Types
 * @psalm-import-type Context from Types
 * @psalm-import-type AppDir from Types
 * @psalm-import-type WriteDir from Types
 * @psalm-import-type LogDir from Types
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type ClassList from Types
 * @psalm-import-type OverwrittenFiles from Types
 * @psalm-import-type CompileReport from Types
 */
final class Compiler
{
    /** @var ArrayObject<int, string> */
    private ArrayObject $classes;
    private AbstractAppMeta $appMeta;

    /** @var Context */
    private string $context;
    private InjectorInterface $injector;
    private CompileAutoload $dumpAutoload;
    private CompileObjectGraph $compilerObjectGraph;

    /**
     * Boot job handed to the preload worker: preload.php records what a boot of this
     * application loads, so the worker has to build the same Meta from the same writeDir.
     *
     * @var array{AppName, Context, AppDir, WriteDir|null}
     */
    private array $preloadJob;

    /**
     * Compile job recorded by fromInjector() and run once in a clean child process.
     * Null on the constructor path.
     *
     * @var array{AppName, Context, AppDir, WriteDir|null}|null
     */
    private array|null $compileJob = null;

    /**
     * @param AppName       $appName  application name "MyVendor|MyProject"
     * @param Context       $context  application context "prod-app"
     * @param AppDir        $appDir   application path
     * @param WriteDir|null $writeDir writable base; defaults to {appDir}/var
     */
    public function __construct(string $appName, string $context, string $appDir, string|null $writeDir = null)
    {
        $meta = AppDirs::meta($appName, $context, $appDir, $writeDir);
        $this->preloadJob = [$meta->name, $context, $appDir, $writeDir];
        // The tracker and hookNullObjectClass must run before the injector is built
        // (building it first would load the app too early and break .compile.php stubs).
        $this->prepare($context, $appDir, $meta);
        // Not factory(): a marker from an earlier compile would hand back a CompiledInjector
        // that FakeRun cannot resolve through, making the result depend on leftover state.
        $this->wire(PackageInjector::compileInjector($meta, $context));
    }

    /**
     * Create a compiler from an application injector.
     *
     * Pass the same $writeDir the injector was built with: __invoke() compiles in a child process
     * that builds its own Meta from it (#482).
     *
     * @param Context       $context
     * @param WriteDir|null $writeDir the one the injector was built with
     *
     * @throws WriteDirMismatchException
     */
    public static function fromInjector(InjectorInterface $injector, string $context, string|null $writeDir = null): self
    {
        $meta = $injector->getInstance(AbstractAppMeta::class);
        /** @var AppDir $appDir */
        $appDir = $meta->appDir;
        $compileTmpDir = AppDirs::meta($meta->name, $context, $appDir, $writeDir)->tmpDir;
        if ($compileTmpDir !== $meta->tmpDir) {
            throw new WriteDirMismatchException($compileTmpDir, $meta->tmpDir);
        }

        $compiler = (new ReflectionClass(self::class))->newInstanceWithoutConstructor();
        $compiler->compileJob = [$meta->name, $context, $appDir, $writeDir];

        return $compiler;
    }

    /**
     * Full compile pipeline: clean tmpDir, compile DI/preload, dump autoload.
     *
     * A compiler created by fromInjector() delegates the whole pipeline to one
     * clean child process and returns that process's exit code.
     */
    public function __invoke(): int
    {
        if ($this->compileJob !== null) {
            return self::compileInChildProcess($this->compileJob);
        }

        $this->clean();
        $this->wire(PackageInjector::compileInjector($this->appMeta, $this->context));
        $report = $this->compile();
        echo PHP_EOL;
        printf("Compilation took %s seconds and used %sMB of memory\n", $report['time'], $report['memory']);
        printf("Compiled: %d resource classes\n", $report['compiled']);
        printf("Preload compile: %s\n", $report['preload']);
        printf("Object graph diagram: %s\n", $report['dot']);

        return $this->dumpAutoload();
    }

    /**
     * Run the constructor compile path once in a clean PHP process.
     *
     * The child prints the compile report itself; only its exit code is returned here.
     *
     * @param array{AppName, Context, AppDir, WriteDir|null} $job
     *
     * @throws PreloadRecordException
     */
    private static function compileInChildProcess(array $job): int
    {
        [$appName, $context, $appDir, $writeDir] = $job;
        $exitCode = 1;
        passthru(self::workerCommand('compile-worker.php', $appName, $context, $appDir, $writeDir), $exitCode);

        return $exitCode;
    }

    /**
     * Write preload.php in a process that boots from the compiled scripts.
     *
     * The compile process cannot measure a boot: it holds the compiler and the module tree,
     * and it loaded the boot path itself before the tracker was installed. Only a process
     * that does nothing but boot the application knows what a request loads.
     *
     * @param array{AppName, Context, AppDir, WriteDir|null} $job
     *
     * @return non-empty-string the generated preload.php
     *
     * @throws PreloadRecordException
     */
    private static function recordPreloadInChildProcess(array $job): string
    {
        [$appName, $context, $appDir, $writeDir] = $job;
        $command = self::workerCommand('preload-worker.php', $appName, $context, $appDir, $writeDir);
        $appDirRealpath = realpath($appDir);
        assert($appDirRealpath !== false);
        $preload = $appDirRealpath . '/preload.php';
        // Remove it first so the check below proves this worker wrote it, not the last deploy.
        @unlink($preload);
        $exitCode = 1;
        passthru($command, $exitCode);
        if ($exitCode !== 0 || ! file_exists($preload)) {
            throw PreloadRecordException::workerFailed($context, $exitCode);
        }

        return $preload;
    }

    /**
     * @param AppName       $appName
     * @param Context       $context
     * @param AppDir        $appDir
     * @param WriteDir|null $writeDir
     *
     * @throws PreloadRecordException
     */
    private static function workerCommand(string $worker, string $appName, string $context, string $appDir, string|null $writeDir): string
    {
        // PHP_BINARY is the server binary under fpm and empty under some embedded SAPIs,
        // and a compile that shells out to those produces an unrelated failure.
        $php = PHP_SAPI === 'cli' ? PHP_BINARY : PHP_BINDIR . '/php';
        if (! is_executable($php)) {
            throw PreloadRecordException::noPhpBinary($php, PHP_SAPI);
        }

        return sprintf(
            '%s %s %s %s %s %s',
            escapeshellarg($php),
            escapeshellarg(dirname(__DIR__) . '/bin/' . $worker),
            escapeshellarg($appName),
            escapeshellarg($context),
            escapeshellarg($appDir),
            escapeshellarg((string) $writeDir),
        );
    }

    /**
     * A compiler created by fromInjector() carries only the compile job, so the
     * in-process pipeline is unavailable on it: fail with intent instead of an
     * uninitialized-property Error.
     */
    private function assertNotDelegated(string $method): void
    {
        if ($this->compileJob === null) {
            return;
        }

        throw new DelegatedCompileException(sprintf(
            '%s() is unavailable on a compiler created by fromInjector(); invoke the compiler itself: Compiler::fromInjector($injector, $context)()',
            $method,
        ));
    }

    /**
     * Remove compiled artifacts from both directories, then recreate the script directory.
     *
     * preload.php and autoload.php go too: a compile that dies before rewriting them would
     * otherwise leave the previous deploy's files looking like this compile's output.
     */
    public function clean(): void
    {
        $this->assertNotDelegated(__FUNCTION__);
        $scriptDir = AppDirs::script($this->appMeta->appDir, $this->context);
        $this->emptyDirectory($this->appMeta->tmpDir);
        $this->emptyDirectory($scriptDir);
        $this->ensureDirectory($scriptDir);
        $appDirRealpath = realpath($this->appMeta->appDir);
        assert($appDirRealpath !== false);
        foreach (['/preload.php', '/autoload.php'] as $generated) {
            @unlink($appDirRealpath . $generated);
        }
    }

    private function emptyDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
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

    private function ensureDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        if (! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException('Unable to create directory: ' . $dir);
        }
    }

    /**
     * Compile application
     *
     * @return CompileReport
     */
    public function compile(): array
    {
        $this->assertNotDelegated(__FUNCTION__);
        $module = (new Module())($this->appMeta, $this->context);
        $compiler = new \Ray\Compiler\Compiler();
        $scriptDir = AppDirs::script($this->appMeta->appDir, $this->context);
        ! is_dir($scriptDir) && ! @mkdir($scriptDir, 0777, true) && ! is_dir($scriptDir);
        $compiler->compile($module, $scriptDir);
        // Marker after final DI scripts so runtime can reuse AOT output (#483).
        CompileMarker::write($scriptDir, $this->appMeta->tmpDir);

        // Compile class meta info (annotations and named parameters)
        $compiled = $this->compileClassMetaInfo();

        // Preload last: the worker boots from the finished artifact - scripts, marker and
        // meta caches all in place - so it loads what a deployed first request loads.
        $preload = self::recordPreloadInChildProcess($this->preloadJob);

        $dot = ($this->compilerObjectGraph)($module);
        $start = self::getRequestTime($_SERVER['REQUEST_TIME_FLOAT'] ?? null);
        $time = number_format(microtime(true) - $start, 2);
        $memory = number_format(memory_get_peak_usage() / (1024 * 1024), 3);
        $dotRealpath = realpath($dot);
        assert($dotRealpath !== false);

        return [
            'time' => $time,
            'memory' => $memory,
            'compiled' => $compiled,
            'preload' => $preload,
            'dot' => $dotRealpath,
        ];
    }

    public function dumpAutoload(): int
    {
        $this->assertNotDelegated(__FUNCTION__);

        return ($this->dumpAutoload)();
    }

    private static function getRequestTime(mixed $requestTime): float
    {
        if (is_float($requestTime)) {
            return $requestTime;
        }

        return 0.0;
    }

    private function compileClassMetaInfo(): int
    {
        $namedParams = $this->injector->getInstance(NamedParameterInterface::class);
        assert($namedParams instanceof NamedParameterInterface);

        $compileClassMetaInfo = new CompileClassMetaInfo();
        $resources = $this->appMeta->getResourceListGenerator();
        $count = 0;
        foreach ($resources as $resource) {
            [$className] = $resource;
            $compileClassMetaInfo($namedParams, $className);
            $count++;
        }

        return $count;
    }

    /**
     * @param Context $context
     * @param AppDir  $appDir
     */
    private function prepare(
        string $context,
        string $appDir,
        AbstractAppMeta $appMeta,
    ): void {
        $tracker = ClassTracker::fromAppDir($appDir);
        $this->classes = $tracker->classes();
        $this->context = $context;
        $this->appMeta = $appMeta;
        $tracker->register();
        $this->hookNullObjectClass($appDir);
    }

    private function wire(InjectorInterface $injector): void
    {
        $this->injector = $injector;
        /** @var AppDir $appDir */
        $appDir = $this->appMeta->appDir;
        /** @var ArrayObject<int, string> $overWritten */
        $overWritten = new ArrayObject();
        $filePutContents = new FilePutContents($overWritten);
        $fakeRun = new FakeRun($injector, $this->context, $this->appMeta);
        $this->dumpAutoload = new CompileAutoload($fakeRun, $filePutContents, $this->classes, $appDir, $this->context);
        $this->compilerObjectGraph = new CompileObjectGraph($filePutContents, $this->appMeta->logDir);
    }

    private function hookNullObjectClass(string $appDir): void
    {
        $appDirRealpath = realpath($appDir);
        assert($appDirRealpath !== false);
        $compileScript = $appDirRealpath . '/.compile.php';
        if (! file_exists($compileScript)) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        require $compileScript;
    }
}

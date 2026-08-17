<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Exception\WriteDirNotAbsoluteException;
use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler\PreloadRecorder;
use BEAR\Package\Exception\DelegatedCompileException;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Exception\WriteDirMismatchException;
use BEAR\Package\Injector\CompiledScripts;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Injector\PackageInjector;
use BEAR\Sunday\Extension\Application\AppInterface;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionMethod;
use RuntimeException;
use SplFileInfo;

use function array_diff;
use function assert;
use function dirname;
use function escapeshellarg;
use function exec;
use function explode;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fileinode;
use function glob;
use function implode;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_float;
use function mkdir;
use function passthru;
use function preg_match_all;
use function preg_quote;
use function rmdir;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function sys_get_temp_dir;
use function trim;
use function uniqid;
use function unlink;
use function var_export;

use const DIRECTORY_SEPARATOR;
use const E_ALL;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;
use const PHP_BINARY;

class CompilerTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/Fake/fake-app';
    private const INDEX_RESOURCE_PATH = 'Resource' . DIRECTORY_SEPARATOR . 'Page' . DIRECTORY_SEPARATOR . 'Index.php';

    public function testInvoke(): void
    {
        $compiledFile1 = self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile3);
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    /** Routing the build through factory() would compile an extra time and log an on-demand compile. */
    public function testInvokeDoesNotEnterTheOnDemandCompilePath(): void
    {
        $errorLog = sys_get_temp_dir() . '/bear-errorlog-' . uniqid('', true) . '.log';
        $previous = (string) ini_get('error_log');
        ini_set('error_log', $errorLog);

        try {
            $code = (new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR))();
        } finally {
            ini_set('error_log', $previous);
        }

        $this->assertSame(0, $code);
        $this->assertStringNotContainsString('Compiled DI scripts on demand', (string) @file_get_contents($errorLog));
        @unlink($errorLog);
    }

    #[Depends('testInvoke')]
    public function testInvokeAgain(): void
    {
        $compiledFile1 = self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    /**
     * The compile stops rather than ship a preload of the compiler.
     *
     * @see PreloadRecorder
     */
    public function testCompileRefusesWhenTheRecordingBootIsNotTheCompiledOne(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'app', self::APP_DIR);

        $this->expectException(PreloadRecordException::class);
        $compiler->compile();
    }

    public function testFromInjectorAndInvoke(): void
    {
        // fromInjector() delegates the compile to one clean child process (constructor path).
        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $compiler = Compiler::fromInjector($injector, 'prod-cli-app');
        $code = $compiler();
        $this->assertSame(0, $code);
        $this->assertDirectoryExists(self::APP_DIR . '/var/build/prod-cli-app/di');
    }

    /**
     * preload.php is recorded by a worker that boots from the compiled scripts, so it holds
     * the boot path and the app's resources - and none of the compiler that produced them.
     */
    public function testConstructorPreloadRecordsAppResourceClasses(): void
    {
        $this->cleanCompileArtifacts('prod-app');
        $this->runCompileProcess('constructor');

        $preload = self::APP_DIR . '/preload.php';
        $autoload = self::APP_DIR . '/autoload.php';
        $this->assertFileExists($preload);
        $contents = (string) file_get_contents($preload);
        $this->assertStringContainsString(self::pathLiteral(self::INDEX_RESOURCE_PATH), $contents);
        $this->assertStringContainsString('require ', $contents);
        $this->assertStringNotContainsString('require_once', $contents);
        $this->assertStringNotContainsString('phpunit', $contents);
        $this->assertStringNotContainsString('compile-stub', $contents);
        $this->assertStringNotContainsString('compile-stub', (string) file_get_contents($autoload));
        $this->assertPreloadHoldsBootNotCompiler($contents);
        $this->assertPreloadCompilesLoadedScripts($contents);
        $this->assertPreloadBodyRuns($preload);
        $this->assertGreaterThan(
            50,
            preg_match_all('/^require(?:_once)? /m', $contents),
            'Constructor compile should record a substantial preload class list',
        );
        $this->assertGeneratedFileCanBeRequired($preload);
        $this->assertGeneratedFileCanBeRequired($autoload);
    }

    /**
     * The boot path is what every request pays for, and the AOT compiler is what no request runs.
     *
     * Recording inside the compile process got both wrong: it missed the boot classes it had
     * already loaded, and it kept the compiler that wrote the scripts. (Ray.Di's assembler is
     * not on this list: a cold start builds the module tree before it reads the scripts.)
     */

    /** Render a path fragment the way the generated file holds it (var_export escapes '\\'). */
    private static function pathLiteral(string $fragment): string
    {
        return trim(var_export($fragment, true), "'");
    }

    private function assertPreloadHoldsBootNotCompiler(string $preload): void
    {
        // Preload belongs to a process that is reused: a CLI one compiles the list, serves its
        // single request and throws it away.
        $this->assertStringContainsString("if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {", $preload);

        foreach (
            [
                'src' . DIRECTORY_SEPARATOR . 'Injector' . DIRECTORY_SEPARATOR . 'PackageInjector.php',
                'src' . DIRECTORY_SEPARATOR . 'Injector' . DIRECTORY_SEPARATOR . 'CompiledScripts.php',
            ] as $bootFile
        ) {
            $this->assertStringContainsString(self::pathLiteral($bootFile), $preload);
        }

        foreach (
            [
                'ray' . DIRECTORY_SEPARATOR . 'compiler' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Compiler.php',
                'ray' . DIRECTORY_SEPARATOR . 'compiler' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'CompileVisitor.php',
                'ray' . DIRECTORY_SEPARATOR . 'aop' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Compiler.php',
            ] as $compilerFile
        ) {
            $this->assertStringNotContainsString(self::pathLiteral($compilerFile), $preload);
        }
    }

    /**
     * DI scripts and AOP proxies are compiled, never required: a DI script builds an instance
     * from variables that only exist in the injector's scope. The list is what the boot loaded,
     * so every proxy's parent is in the require list above it and PHP can link them all.
     */
    private function assertPreloadCompilesLoadedScripts(string $preload): void
    {
        $diScript = 'var' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'prod-app'
            . DIRECTORY_SEPARATOR . 'di' . DIRECTORY_SEPARATOR;
        $this->assertStringContainsString("if (function_exists('opcache_compile_file')", $preload);
        $this->assertGreaterThan(
            0,
            preg_match_all('/^ {4}opcache_compile_file\(.*' . preg_quote(self::pathLiteral($diScript), '/') . '/m', $preload),
            'Preload must compile the DI scripts the boot loaded',
        );
        foreach (explode("\n", $preload) as $line) {
            if (! str_starts_with($line, 'require ')) {
                continue;
            }

            $this->assertStringNotContainsString(
                self::pathLiteral($diScript),
                $line,
                'A DI script must never be required: it runs against the injector\'s scope',
            );
        }
    }

    /**
     * Skeleton-style compile entry builds the injector first, then calls fromInjector().
     * fromInjector() compiles in a clean child process, so the class-tracking autoloader
     * is installed before any app class loads and records the same app resource classes.
     * No PHPUnit class can leak into the preload list from the clean child either.
     *
     * @see https://github.com/bearsunday/BEAR.Package/issues/482
     */
    public function testFromInjectorPreloadRecordsAppResourceClasses(): void
    {
        $this->cleanCompileArtifacts('prod-app');
        $this->runCompileProcess('from-injector');

        $preload = self::APP_DIR . '/preload.php';
        $autoload = self::APP_DIR . '/autoload.php';
        $this->assertFileExists($preload);
        $contents = (string) file_get_contents($preload);
        $this->assertStringContainsString(
            self::pathLiteral(self::INDEX_RESOURCE_PATH),
            $contents,
            'fromInjector compile must record app resource classes loaded during FakeRun/loadResources',
        );
        $this->assertStringContainsString('require ', $contents);
        $this->assertStringNotContainsString('require_once', $contents);
        $this->assertStringNotContainsString('phpunit', $contents);
        $this->assertGreaterThan(
            50,
            preg_match_all('/^require(?:_once)? /m', $contents),
            'fromInjector compile should record a substantial preload class list (not only classes loaded after tracking starts)',
        );
        $this->assertStringNotContainsString('compile-stub', $contents);
        $this->assertStringNotContainsString('compile-stub', (string) file_get_contents($autoload));
        $this->assertGeneratedFileCanBeRequired($preload);
        $this->assertGeneratedFileCanBeRequired($autoload);
    }

    /**
     * Both entries measure the same tracker-recorded population, so the fromInjector
     * preload must contain every path the constructor preload has. (The child process
     * may add the compile entry's own classes on top.)
     */
    public function testFromInjectorPreloadContainsConstructorPreloadPaths(): void
    {
        $this->cleanCompileArtifacts('prod-app');
        $this->runCompileProcess('constructor');
        $constructorPaths = $this->getPreloadPaths();
        $this->cleanCompileArtifacts('prod-app');
        $this->runCompileProcess('from-injector');
        $fromInjectorPaths = $this->getPreloadPaths();
        $missing = array_diff($constructorPaths, $fromInjectorPaths);
        $this->assertSame([], $missing, 'fromInjector preload must contain every constructor preload path');
    }

    /**
     * A writeDir takes the writable directories, one place per app and context. Compiled scripts
     * are not among them: they ship in the deployment artifact, under appDir.
     */
    public function testConstructorWritesUnderWriteDirAndKeepsScriptsUnderAppDir(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-package-write-' . uniqid();
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, $writeDir);
        $this->assertSame(0, $compiler());
        $app = $writeDir . '/FakeVendor/HelloWorld/prod-cli-app';
        $this->assertFileExists(self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php');
        $this->assertFileExists($app . '/log/module.dot');
        $this->assertDirectoryExists($app . '/tmp');
        $this->assertFileDoesNotExist($app . '/tmp/di/FakeVendor_HelloWorld_Resource_Page_Index-.php');
    }

    /**
     * The compile runs in a child process that builds its own Meta, so the paths must come out
     * identical to the injector's - not layered a second time.
     */
    public function testFromInjectorWritesWhereTheInjectorWrites(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-package-write-' . uniqid();
        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR, null, $writeDir);
        $meta = $injector->getInstance(AbstractAppMeta::class);
        $app = $writeDir . '/FakeVendor/HelloWorld/prod-cli-app';
        $this->assertSame($app . '/tmp', $meta->tmpDir);

        $this->assertSame(0, Compiler::fromInjector($injector, 'prod-cli-app', $writeDir)());
        $this->assertFileExists(self::APP_DIR . '/var/build/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php');
        $this->assertFileExists($app . '/log/module.dot');
        $this->assertDirectoryDoesNotExist($app . '/tmp/FakeVendor');
    }

    /**
     * Passing the write directory to one side only compiles for the default paths, and every boot
     * then finds scripts compiled for somewhere else and recompiles.
     */
    public function testFromInjectorRejectsAWriteDirTheInjectorDoesNotUse(): void
    {
        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR, null, sys_get_temp_dir() . '/bear-package-write-' . uniqid());
        $this->expectException(WriteDirMismatchException::class);
        Compiler::fromInjector($injector, 'prod-cli-app');
    }

    /** A relative or empty write directory resolves against the current directory, which differs between compile and request. */
    public function testWriteDirMustBeAbsolute(): void
    {
        $this->expectException(WriteDirNotAbsoluteException::class);
        new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, 'relative/dir');
    }

    /** A boot after a compile with the same writeDir reuses the scripts instead of writing them again. */
    public function testBootAfterCompileWithSameWriteDirDoesNotRewriteScripts(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-package-write-' . uniqid();
        $this->assertSame(0, (new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, $writeDir))());
        $scriptDir = CompiledScripts::dir(self::APP_DIR, 'prod-cli-app');
        $scripts = glob($scriptDir . '/*.php');
        $this->assertNotFalse($scripts);
        $this->assertNotSame([], $scripts);
        $inodes = [];
        foreach ($scripts as $file) {
            $inodes[$file] = fileinode($file);
        }

        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR, null, $writeDir);
        $injector->getInstance(AppInterface::class);
        foreach ($inodes as $file => $inode) {
            $this->assertSame($inode, fileinode($file), $file . ' was rewritten');
        }
    }

    /** Compile, then pack: the archive carries the scripts and their marker. */
    public function testPharPacksTheCompiledScripts(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-package-write-' . uniqid();
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, $writeDir);
        $this->assertSame(0, $compiler());
        $this->assertSame(0, $compiler->phar());

        $phar = self::APP_DIR . '/var/build/prod-cli-app.phar';
        $this->assertFileExists($phar);
        $this->assertTrue(file_exists('phar://' . $phar . '/var/build/prod-cli-app/di/' . CompileMarker::FILENAME));
        $this->assertTrue(file_exists('phar://' . $phar . '/src/Module/AppModule.php'));
        $this->assertFalse(file_exists('phar://' . $phar . '/autoload.php'));
    }

    /** The stub would require a path that is not there, so the entry is checked before the worker starts. */
    public function testPharEntryThatDoesNotExist(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, sys_get_temp_dir() . '/bear-package-write-' . uniqid());

        $this->expectException(PharEntryNotFoundException::class);
        $compiler->phar('public/nowhere.php');
    }

    /**
     * The delegating compiler holds only the compile job, so an in-process pipeline
     * call must fail with intent rather than an uninitialized-property Error.
     */
    public function testDelegatedCompilerRejectsInProcessCompile(): void
    {
        $meta = new Meta(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $injector = PackageInjector::factory($meta, 'prod-cli-app');
        $compiler = Compiler::fromInjector($injector, 'prod-cli-app');
        $this->expectException(DelegatedCompileException::class);
        $compiler->compile();
    }

    /** @return list<string> */
    private function getPreloadPaths(): array
    {
        $contents = (string) file_get_contents(self::APP_DIR . '/preload.php');
        preg_match_all('/^require (.+);$/m', $contents, $matches);

        return $matches[1];
    }

    private function runCompileProcess(string $factory): void
    {
        $command = sprintf(
            '%s %s %s',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(__DIR__ . '/script/compile.php'),
            escapeshellarg($factory),
        );
        passthru($command, $exitCode);
        $this->assertSame(0, $exitCode, 'Fixture compilation must succeed in a clean PHP process');
    }

    private function assertGeneratedFileCanBeRequired(string $file): void
    {
        $code = sprintf('require %s;', var_export($file, true));
        $command = sprintf('%s -d display_errors=1 -r %s', escapeshellarg(PHP_BINARY), escapeshellarg($code));
        passthru($command, $exitCode);
        $this->assertSame(0, $exitCode, 'Generated PHP file must be require-able in a clean PHP process: ' . $file);
    }

    /**
     * Run every entry of preload.php, which requiring the file under CLI never does.
     *
     * The generated SAPI guard returns before the first require, so a redeclare, a missing
     * parent or an unlinked proxy would go unseen here. The body is run beside the original so
     * `__DIR__` still resolves, with every diagnostic promoted to a failure.
     */
    private function assertPreloadBodyRuns(string $preload): void
    {
        $contents = (string) file_get_contents($preload);
        $guard = "if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {\n    return;\n}\n";
        $this->assertStringContainsString($guard, $contents);
        $body = dirname($preload) . '/preload-body-test.php';
        file_put_contents($body, str_replace($guard, '', $contents));
        $command = sprintf(
            '%s -d error_reporting=%d -d display_errors=1 -r %s',
            escapeshellarg(PHP_BINARY),
            E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED,
            escapeshellarg(sprintf('require %s;', var_export($body, true))),
        );
        exec($command . ' 2>&1', $output, $exitCode);
        @unlink($body);
        $this->assertSame(0, $exitCode, 'preload.php body must run: ' . implode("\n", $output));
        $this->assertSame([], $output, 'preload.php body must run without a diagnostic');
    }

    /**
     * Wipe compile outputs without constructing Compiler (which would preload classes
     * and spoil FakeRun class-tracking measurements in this process).
     *
     * @param non-empty-string $context
     */
    private function cleanCompileArtifacts(string $context): void
    {
        @unlink(self::APP_DIR . '/preload.php');
        @unlink(self::APP_DIR . '/autoload.php');
        $this->removeTree(self::APP_DIR . '/var/tmp/' . $context);
        $this->removeTree(CompiledScripts::dir(self::APP_DIR, $context));
    }

    private function removeTree(string $dir): void
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

    public function testCleanRemovesArtifactsAndRecreatesDirs(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $nested = self::APP_DIR . '/var/tmp/prod-cli-app/nested';
        if (! is_dir($nested)) {
            mkdir($nested, 0777, true);
        }

        $scriptDir = CompiledScripts::dir(self::APP_DIR, 'prod-cli-app');
        if (! is_dir($scriptDir)) {
            mkdir($scriptDir, 0777, true);
        }

        $marker = $nested . '/marker.txt';
        $stale = $scriptDir . '/Stale_Script-.php';
        file_put_contents($marker, 'x');
        file_put_contents($stale, 'x');
        $compiler->clean();
        $this->assertFileDoesNotExist($marker);
        $this->assertDirectoryDoesNotExist($nested);
        $this->assertFileDoesNotExist($stale);
        $this->assertDirectoryExists($scriptDir);
    }

    public function testEmptyDirectoryWhenMissingIsNoOp(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $emptyDirectory = new ReflectionMethod(Compiler::class, 'emptyDirectory');
        $emptyDirectory->invoke($compiler, sys_get_temp_dir() . '/bear-package-missing-' . uniqid());
        $this->addToAssertionCount(1);
    }

    public function testEnsureDirectoryWhenExistsIsNoOp(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $ensureDirectory = new ReflectionMethod(Compiler::class, 'ensureDirectory');
        $ensureDirectory->invoke($compiler, sys_get_temp_dir());
        $this->addToAssertionCount(1);
    }

    public function testEnsureDirectoryThrowsWhenUncreatable(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $ensureDirectory = new ReflectionMethod(Compiler::class, 'ensureDirectory');
        $file = sys_get_temp_dir() . '/bear-package-not-dir-' . uniqid();
        file_put_contents($file, 'x');
        try {
            $this->expectException(RuntimeException::class);
            $ensureDirectory->invoke($compiler, $file . '/child');
        } finally {
            @unlink($file);
        }
    }

    public function testWrongAppDir(): void
    {
        $this->expectException(RuntimeException::class);
        (new Compiler(self::APP_NAME, 'app', '__invalid__'))->compile();
    }

    public function testUnbound(): void
    {
        $this->expectException(Unbound::class);
        $compiler = new Compiler(self::APP_NAME, 'cli-unbound-app', self::APP_DIR);
        $compiler->compile();
    }

    public function testInvalidConetxt(): void
    {
        $this->expectException(InvalidContextException::class);
        $compiler = new Compiler(self::APP_NAME, 'cli-invalid-app', self::APP_DIR);
        $compiler->compile();
    }

    public function testGetRequestTimeFallsBackToZeroForNonFloat(): void
    {
        // $_SERVER['REQUEST_TIME_FLOAT'] is normally a float, but guard against a malformed value.
        $getRequestTime = new ReflectionMethod(Compiler::class, 'getRequestTime');
        $result = $getRequestTime->invoke(null, 'not-a-float');
        assert(is_float($result));
        $this->assertSame(0.0, $result);
    }
}

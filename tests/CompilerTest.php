<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\DelegatedCompileException;
use BEAR\Package\Exception\InvalidContextException;
use BEAR\Package\Exception\InvalidWriteDirException;
use BEAR\Package\Exception\WriteDirMismatchException;
use BEAR\Package\Injector\AppDirs;
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
use function escapeshellarg;
use function file_get_contents;
use function file_put_contents;
use function fileinode;
use function glob;
use function ini_get;
use function ini_set;
use function is_dir;
use function is_float;
use function mkdir;
use function passthru;
use function preg_match_all;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function var_export;

use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;

class CompilerTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/Fake/fake-app';
    private const INDEX_RESOURCE_PATH = 'Resource' . DIRECTORY_SEPARATOR . 'Page' . DIRECTORY_SEPARATOR . 'Index.php';

    public function testInvoke(): void
    {
        $compiledFile1 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
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
        $compiledFile1 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    public function testFromInjectorAndInvoke(): void
    {
        // fromInjector() delegates the compile to one clean child process (constructor path).
        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $compiler = Compiler::fromInjector($injector, 'prod-cli-app');
        $code = $compiler();
        $this->assertSame(0, $code);
        $this->assertDirectoryExists(self::APP_DIR . '/var/tmp/prod-cli-app/di');
    }

    /**
     * Constructor path installs the class-tracking autoloader before the app is loaded,
     * so FakeRun + loadResources record real runtime classes into preload.php.
     */
    public function testConstructorPreloadRecordsAppResourceClasses(): void
    {
        $this->cleanCompileArtifacts('prod-app');
        $this->runCompileProcess('constructor');

        $preload = self::APP_DIR . '/preload.php';
        $autoload = self::APP_DIR . '/autoload.php';
        $this->assertFileExists($preload);
        $contents = (string) file_get_contents($preload);
        $this->assertStringContainsString(self::INDEX_RESOURCE_PATH, $contents);
        $this->assertStringContainsString('require ', $contents);
        $this->assertStringNotContainsString('require_once', $contents);
        $this->assertStringNotContainsString('phpunit', $contents);
        $this->assertStringNotContainsString('compile-stub', $contents);
        $this->assertStringNotContainsString('compile-stub', (string) file_get_contents($autoload));
        $this->assertGreaterThan(
            50,
            preg_match_all('/^require(?:_once)? /m', $contents),
            'Constructor compile should record a substantial preload class list',
        );
        $this->assertGeneratedFileCanBeRequired($preload);
        $this->assertGeneratedFileCanBeRequired($autoload);
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
            self::INDEX_RESOURCE_PATH,
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
        $this->assertFileExists(self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php');
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
        $this->assertFileExists(self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php');
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
        $this->expectException(InvalidWriteDirException::class);
        new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, 'relative/dir');
    }

    /** A boot after a compile with the same writeDir reuses the scripts instead of writing them again. */
    public function testBootAfterCompileWithSameWriteDirDoesNotRewriteScripts(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-package-write-' . uniqid();
        $this->assertSame(0, (new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, $writeDir))());
        $scriptDir = AppDirs::script(self::APP_DIR, 'prod-cli-app');
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
     * Wipe compile outputs without constructing Compiler (which would preload classes
     * and spoil FakeRun class-tracking measurements in this process).
     *
     * @param non-empty-string $context
     */
    private function cleanCompileArtifacts(string $context): void
    {
        @unlink(self::APP_DIR . '/preload.php');
        @unlink(self::APP_DIR . '/autoload.php');
        $tmpDir = self::APP_DIR . '/var/tmp/' . $context;
        if (! is_dir($tmpDir)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tmpDir, FilesystemIterator::SKIP_DOTS),
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
        $tmpDir = self::APP_DIR . '/var/tmp/prod-cli-app';
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $nested = $tmpDir . '/nested';
        if (! is_dir($nested)) {
            mkdir($nested, 0777, true);
        }

        $marker = $nested . '/marker.txt';
        file_put_contents($marker, 'x');
        $compiler->clean();
        $this->assertFileDoesNotExist($marker);
        $this->assertDirectoryDoesNotExist($nested);
        $this->assertDirectoryExists($tmpDir . '/di');
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

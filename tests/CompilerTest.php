<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Exception\InvalidContextException;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Di\Exception\Unbound;
use ReflectionMethod;
use RuntimeException;

use function assert;
use function file_put_contents;
use function is_dir;
use function is_float;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class CompilerTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/Fake/fake-app';

    public function testInvoke(): void
    {
        $compiledFile1 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile3);
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    #[Depends('testInvoke')]
    public function testInvokeAgain(): void
    {
        $compiledFile1 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = self::APP_DIR . '/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    public function testFromInjectorAndRun(): void
    {
        // Establish injector the same way as new Compiler() so .compile.php stubs load first.
        new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
        $injector = Injector::getInstance(self::APP_NAME, 'prod-cli-app', self::APP_DIR);
        $compiler = Compiler::fromInjector($injector, 'prod-cli-app', false);
        $code = $compiler->run();
        $this->assertSame(0, $code);
        $this->assertDirectoryExists(self::APP_DIR . '/var/tmp/prod-cli-app/di');
    }

    public function testCleanRemovesArtifactsAndRecreatesDirs(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
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
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
        $emptyDirectory = new ReflectionMethod(Compiler::class, 'emptyDirectory');
        $emptyDirectory->invoke($compiler, sys_get_temp_dir() . '/bear-package-missing-' . uniqid());
        $this->addToAssertionCount(1);
    }

    public function testEnsureDirectoryWhenExistsIsNoOp(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
        $ensureDirectory = new ReflectionMethod(Compiler::class, 'ensureDirectory');
        $ensureDirectory->invoke($compiler, sys_get_temp_dir());
        $this->addToAssertionCount(1);
    }

    public function testEnsureDirectoryThrowsWhenUncreatable(): void
    {
        $compiler = new Compiler(self::APP_NAME, 'prod-cli-app', self::APP_DIR, false);
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
        $compiler = new Compiler(self::APP_NAME, 'cli-unbound-app', self::APP_DIR, false);
        $compiler->compile();
    }

    public function testInvalidConetxt(): void
    {
        $this->expectException(InvalidContextException::class);
        $compiler = new Compiler(self::APP_NAME, 'cli-invalid-app', self::APP_DIR, false);
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

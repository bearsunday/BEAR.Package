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
use function is_float;
use function unlink;

class CompilerTest extends TestCase
{
    public function testInvoke(): void
    {
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile3);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app', false);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    #[Depends('testInvoke')]
    public function testInvokeAgain(): void
    {
        $compiled = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/compiled';
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        $compiler = new Compiler('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app', false);
        $report = $compiler->compile();
        $this->assertGreaterThan(0, $report['compiled']);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    public function testWrongAppDir(): void
    {
        $this->expectException(RuntimeException::class);
        (new Compiler('FakeVendor\HelloWorld', 'app', '__invalid__'))->compile();
    }

    public function testUnbound(): void
    {
        $this->expectException(Unbound::class);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'cli-unbound-app', __DIR__ . '/Fake/fake-app', false);
        $code = $compiler->compile();
    }

    public function testInvalidConetxt(): void
    {
        $this->expectException(InvalidContextException::class);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'cli-invalid-app', __DIR__ . '/Fake/fake-app', false);
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

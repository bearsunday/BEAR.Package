<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Exception\InvalidContextException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function error_log;
use function unlink;

class CompilerTest extends TestCase
{
    public function setUp(): void
    {
        $this->setOutputCallback(static function (string $msg) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($msg);
        });
    }

    public function testInvoke(): void
    {
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile3);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app', false);
        $status = $compiler->compile();
        $this->assertSame(0, $status);
        $compiler->dumpAutoload();
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile3);
    }

    public function testInvokeAgain(): void
    {
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile3 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile3);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app', false);
        $compiler->compile();
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
        $compiler = new Compiler('FakeVendor\HelloWorld', 'cli-unbound-app', __DIR__ . '/Fake/fake-app', false);
        $code = $compiler->compile();
        $this->assertSame(1, $code);
    }

    public function testInvalidConetxt(): void
    {
        $this->expectException(InvalidContextException::class);
        $compiler = new Compiler('FakeVendor\HelloWorld', 'cli-invalid-app', __DIR__ . '/Fake/fake-app', false);
        $compiler->compile();
    }
}

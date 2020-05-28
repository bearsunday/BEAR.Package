<?php

declare(strict_types=1);

namespace BEAR\Package;

use PHPUnit\Framework\TestCase;
use Ray\Compiler\ScriptInjector;

class CompilerTest extends TestCase
{
    public function testInvoke() : void
    {
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        $compiledFile2 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di' . ScriptInjector::MODULE;
        $compiledFile3 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_FakeFoo-.php';
        @unlink($compiledFile1);
        @unlink($compiledFile2);
        @unlink($compiledFile3);
        (new Compiler)('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app');
        $this->assertFileExists($compiledFile1);
        $this->assertFileExists($compiledFile2);
        $this->assertFileExists($compiledFile3);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package;

use PHPUnit\Framework\TestCase;

class CompilerTest extends TestCase
{
    public function testInvoke()
    {
        $compiledFile1 = __DIR__ . '/Fake/fake-app/var/tmp/prod-cli-app/di/FakeVendor_HelloWorld_Resource_Page_Index-.php';
        @unlink($compiledFile1);
        (new Compiler)('FakeVendor\HelloWorld', 'prod-cli-app', __DIR__ . '/Fake/fake-app');
        $this->assertFileExists($compiledFile1);
    }
}

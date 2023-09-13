<?php

declare(strict_types=1);

namespace BEAR\Package;

use BEAR\Package\Compiler\CompileApp;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\CompileInjector;

use function assert;

class CompileAppTest extends TestCase
{
    public function testCompile(): void
    {
        $injector = Injector::getInstance('FakeVendor\MinApp', 'prod-app', __DIR__ . '/Fake/fake-min-app');
        assert($injector instanceof CompileInjector);
        $logs = (new CompileApp())->compile($injector, ['prod-api-app']);
        $this->assertSame(1, $logs['method']);
    }
}

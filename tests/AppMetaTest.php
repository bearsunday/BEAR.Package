<?php

namespace BEAR\Package;

class AppMetaTest extends \PHPUnit_Framework_TestCase
{
    public function testAppMeta()
    {
        $appName = 'FakeVendor\HelloWorld';
        $appMeta = new AppMeta($appName);
        $this->assertSame($appName, $appMeta->name);
        $expectAppDir = __DIR__ . '/Fake/fake-app';
        $this->assertSame($expectAppDir, $appMeta->appDir);
        $this->assertSame($expectAppDir . '/var/tmp', $appMeta->tmpDir);
        $this->assertSame($expectAppDir . '/var/log', $appMeta->logDir);
    }
}

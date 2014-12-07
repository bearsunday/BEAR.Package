<?php

namespace BEAR\Package;

class AppReflectorTest extends \PHPUnit_Framework_TestCase
{
    public function testAppReflectorResourceList()
    {
        $appMeta = new AppMeta('FakeVendor\HelloWorld');
        $appReflector = new AppReflector($appMeta);
        foreach ($appReflector->resourceList() as list($class, $file)) {
            $classes[] = $class;
            $files[] = $file;
        }
        $expect = [
            'FakeVendor\HelloWorld\Resource\App\One',
            'FakeVendor\HelloWorld\Resource\App\Two',
            'FakeVendor\HelloWorld\Resource\Page\Index'
        ];
        $this->assertSame($expect, $classes);
        $expect = [
            $appMeta->appDir . '/Resource/App/One.php',
            $appMeta->appDir . '/Resource/App/Two.php',
            $appMeta->appDir . '/Resource/Page/Index.php',
        ];
        $this->assertSame($expect, $files);
    }
}

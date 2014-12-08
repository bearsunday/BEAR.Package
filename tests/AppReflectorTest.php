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
            'FakeVendor\HelloWorld\Resource\Page\Index',
            'FakeVendor\HelloWorld\Resource\App\Sub\Three',
            'FakeVendor\HelloWorld\Resource\App\Sub\Sub\Four'        ];
        $this->assertSame($expect, $classes);
        $expect = [
            $appMeta->appDir . '/src/Resource/App/One.php',
            $appMeta->appDir . '/src/Resource/App/Two.php',
            $appMeta->appDir . '/src/Resource/Page/Index.php',
            $appMeta->appDir . '/src/Resource/App/Sub/Three.php',
            $appMeta->appDir . '/src/Resource/App/Sub/Sub/Four.php'
        ];
        $this->assertSame($expect, $files);
    }
}

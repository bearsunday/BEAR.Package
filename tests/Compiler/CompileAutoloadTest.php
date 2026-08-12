<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\AppMeta\Meta;
use BEAR\Package\Fake\Preload\SameFileClass;
use BEAR\Package\Fake\Preload\SameFileInterface;
use BEAR\Package\FakeUnusedInjector;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function class_exists;
use function dirname;
use function interface_exists;
use function sys_get_temp_dir;
use function uniqid;

require_once dirname(__DIR__) . '/Fake/preload/SameFileSymbols.php';

class CompileAutoloadTest extends TestCase
{
    public function testGetPathsDeduplicatesSymbolsFromTheSameFile(): void
    {
        $this->assertTrue(interface_exists(SameFileInterface::class, false));
        $this->assertTrue(class_exists(SameFileClass::class, false));

        $autoload = $this->newCompileAutoload();
        $paths = $autoload->getPaths([SameFileInterface::class, SameFileClass::class]);
        $this->assertCount(1, $paths);
    }

    public function testGetRelativePathKeepsAbsolutePathOutsideAppDir(): void
    {
        $autoload = $this->newCompileAutoload();
        $getRelativePath = new ReflectionMethod(CompileAutoload::class, 'getRelativePath');
        $outside = sys_get_temp_dir() . '/bear-package-' . uniqid('', true) . '.php';
        $result = $getRelativePath->invoke($autoload, dirname(__DIR__, 2), $outside);
        $this->assertSame("'" . $outside . "'", $result);
    }

    private function newCompileAutoload(): CompileAutoload
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $classes = new ArrayObject();
        $filePutContents = new FilePutContents(new ArrayObject());
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);
        $injector = new FakeUnusedInjector();
        $fakeRun = new FakeRun($injector, 'prod-app', $meta);

        return new CompileAutoload($fakeRun, $filePutContents, $classes, $appDir, 'prod-app');
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\DirectoryNotWritableException;
use BEAR\Package\Injector;
use BEAR\Resource\ResourceInterface;
use BEAR\Sunday\Extension\Application\AppInterface;
use Exception;
use FakeVendor\HelloWorld\FakeDep;
use FakeVendor\HelloWorld\FakeDep2;
use FakeVendor\HelloWorld\FakeDepInterface;
use FakeVendor\HelloWorld\Resource\Page\Injection;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;
use Ray\Compiler\CompiledInjector;
use Ray\Di\AbstractModule;
use Ray\Di\Injector as RayInjector;
use Ray\Di\InjectorInterface;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Throwable;

use function assert;
use function dirname;
use function file_exists;
use function fileinode;
use function filemtime;
use function glob;
use function hash;
use function is_int;
use function is_string;
use function mkdir;
use function restore_error_handler;
use function rmdir;
use function set_error_handler;
use function str_contains;
use function str_ends_with;
use function sys_get_temp_dir;
use function time;
use function touch;
use function uniqid;
use function unlink;

use const E_USER_NOTICE;
use const E_USER_WARNING;

class PackageInjectorTest extends TestCase
{
    /** Remove the compile marker and DI scripts so a prod factory() starts cold. */
    private static function cleanProdDi(string $scriptDir): void
    {
        @unlink(CompileMarker::path($scriptDir));
        foreach (glob($scriptDir . '/*.php') ?: [] as $file) {
            @unlink($file);
        }
    }

    public function testOriginalBind(): void
    {
        $injector = Injector::getInstance('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $page = $resource->newInstance('page://self/injection');
        assert($page instanceof Injection);
        $this->assertInstanceOf(FakeDep::class, $page->foo);
    }

    #[Depends('testOriginalBind')]
    public function testGetOverrideInstance(): void
    {
        $injector = Injector::getOverrideInstance('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app', new class extends AbstractModule{
            protected function configure(): void
            {
                $this->bind(FakeDepInterface::class)->to(FakeDep2::class);
            }
        });
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $page = $resource->newInstance('page://self/injection');
        assert($page instanceof Injection);
        $this->assertInstanceOf(FakeDep2::class, $page->foo);
    }

    public function testOverrideScriptDirIsIsolatedFromDefault(): void
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $defaultInjector = Injector::getInstance('FakeVendor\HelloWorld', 'app', $appDir);
        assert($defaultInjector instanceof RayInjector);
        $defaultDir = (new ReflectionProperty(RayInjector::class, 'classDir'))->getValue($defaultInjector);
        assert(is_string($defaultDir));
        $this->assertTrue(str_ends_with($defaultDir, '/di'));

        $overrideModule = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeDepInterface::class)->to(FakeDep2::class);
            }
        };
        $overrideInjector = Injector::getOverrideInstance('FakeVendor\HelloWorld', 'app', $appDir, $overrideModule);
        assert($overrideInjector instanceof RayInjector);
        $overrideDir = (new ReflectionProperty(RayInjector::class, 'classDir'))->getValue($overrideInjector);
        assert(is_string($overrideDir));

        $expectedSuffix = '/di/' . hash('xxh128', $overrideModule::class);
        $this->assertTrue(str_ends_with($overrideDir, $expectedSuffix));
        $this->assertNotSame($defaultDir, $overrideDir);
    }

    public function testDifferentOverrideModulesUseDifferentScriptDirs(): void
    {
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $moduleA = new class extends AbstractModule {
            protected function configure(): void
            {
                $this->bind(FakeDepInterface::class)->to(FakeDep2::class);
            }
        };
        $moduleB = new class extends AbstractModule {
            protected function configure(): void
            {
            }
        };

        $injectorA = Injector::getOverrideInstance('FakeVendor\HelloWorld', 'app', $appDir, $moduleA);
        $injectorB = Injector::getOverrideInstance('FakeVendor\HelloWorld', 'app', $appDir, $moduleB);
        assert($injectorA instanceof RayInjector);
        assert($injectorB instanceof RayInjector);

        $dirA = (new ReflectionProperty(RayInjector::class, 'classDir'))->getValue($injectorA);
        $dirB = (new ReflectionProperty(RayInjector::class, 'classDir'))->getValue($injectorB);
        assert(is_string($dirA));
        assert(is_string($dirB));

        $this->assertNotSame($dirA, $dirB);
        $this->assertTrue(str_ends_with($dirA, '/di/' . hash('xxh128', $moduleA::class)));
        $this->assertTrue(str_ends_with($dirB, '/di/' . hash('xxh128', $moduleB::class)));
    }

    #[Depends('testGetOverrideInstance')]
    public function testDefaultInjectorUnaffectedAfterOverride(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);

        $injector = Injector::getInstance('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $resource = $injector->getInstance(ResourceInterface::class);
        assert($resource instanceof ResourceInterface);
        $page = $resource->newInstance('page://self/injection');
        assert($page instanceof Injection);
        $this->assertInstanceOf(FakeDep::class, $page->foo);
    }

    public function testDiagnoseCacheFailureForSerializationError(): void
    {
        $diagnoseCacheFailure = new ReflectionMethod(PackageInjector::class, 'diagnoseCacheFailure');
        $message = $diagnoseCacheFailure->invoke(null, new ThrowOnSerializeInjector(), 'injector-id');
        assert(is_string($message));

        $this->assertStringContainsString('Serialization failed: serialize failed', $message);
    }

    public function testCacheStorageFailureMessage(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);

        // Only the cache diagnostic is asserted here, whatever else may be reported.
        set_error_handler(static function (int $errno, string $errstr): bool {
            if (! str_contains($errstr, 'Failed to cache the injector')) {
                return true;
            }

            throw new Exception($errstr, $errno);
        }, E_USER_WARNING);

        try {
            $this->expectExceptionMessage('The cache adapter could not store the item.');
            $injector = PackageInjector::getInstance(new Meta('FakeVendor\MinApp'), 'prod-app', new NullAdapter());
            $this->assertInstanceOf(InjectorInterface::class, $injector);
        } finally {
            restore_error_handler();
        }
    }

    public function testIsProdReturnsFalseWhenCompileIsUnbound(): void
    {
        // Covers the Unbound catch branch of PackageInjector::isProd().
        // A minimal module that never installs DiCompileModule leaves
        // Compile::class unbound, so getInstance('', Compile::class) throws.
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
            }
        };

        $isProd = new ReflectionMethod(PackageInjector::class, 'isProd');
        $this->assertFalse($isProd->invoke(null, $module));
    }

    public function testScriptDirWithoutOverride(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $scriptDir = new ReflectionMethod(PackageInjector::class, 'scriptDir');
        $this->assertSame($meta->tmpDir . '/di', $scriptDir->invoke(null, $meta, null));
    }

    public function testScriptDirWithOverride(): void
    {
        $meta = new Meta('FakeVendor\HelloWorld', 'app', dirname(__DIR__) . '/Fake/fake-app');
        $module = new class extends AbstractModule {
            protected function configure(): void
            {
            }
        };
        $scriptDir = new ReflectionMethod(PackageInjector::class, 'scriptDir');
        $this->assertSame(
            $meta->tmpDir . '/di/' . hash('xxh128', $module::class),
            $scriptDir->invoke(null, $meta, $module),
        );
    }

    public function testProdFactoryReusesAotScriptsWhenMarkerPresent(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);
        $scriptDir = $meta->tmpDir . '/di';
        self::cleanProdDi($scriptDir);

        // First factory compiles on demand and emits E_USER_NOTICE; swallow it so
        // the cold-start notice does not leak into this test's output.
        set_error_handler(static function (): bool {
            return true;
        }, E_USER_NOTICE);
        try {
            $first = PackageInjector::factory($meta, 'prod-app');
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(CompiledInjector::class, $first);
        $this->assertTrue(file_exists(CompileMarker::path($scriptDir)));

        $phpScripts = glob($scriptDir . '/*.php');
        $this->assertNotFalse($phpScripts);
        $this->assertNotSame([], $phpScripts);
        $inodes = [];
        foreach ($phpScripts as $file) {
            $inodes[$file] = fileinode($file);
        }

        $second = PackageInjector::factory($meta, 'prod-app');
        $this->assertInstanceOf(CompiledInjector::class, $second);
        $second->getInstance(AppInterface::class);
        foreach ($inodes as $file => $inode) {
            $this->assertSame($inode, fileinode($file), $file . ' was rewritten');
        }
    }

    public function testProdFactoryNoticesAndCompilesWhenMarkerMissing(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);
        $scriptDir = $meta->tmpDir . '/di';
        self::cleanProdDi($scriptDir);

        $notices = [];
        set_error_handler(static function (int $errno, string $errstr) use (&$notices): bool {
            $notices[] = $errstr;

            return true;
        }, E_USER_NOTICE);

        try {
            $injector = PackageInjector::factory($meta, 'prod-app');
        } finally {
            restore_error_handler();
        }

        $this->assertInstanceOf(CompiledInjector::class, $injector);
        $injector->getInstance(AppInterface::class);
        $this->assertNotEmpty($notices);
        $this->assertStringContainsString('Not precompiled', $notices[0]);
        $this->assertTrue(file_exists(CompileMarker::path($scriptDir)));
    }

    public function testProdFactoryIgnoresSourceChangesWhenMarkerPresent(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);
        $scriptDir = $meta->tmpDir . '/di';

        PackageInjector::factory($meta, 'prod-app');
        $phpScripts = glob($scriptDir . '/*.php');
        $this->assertNotFalse($phpScripts);
        $inodes = [];
        foreach ($phpScripts as $file) {
            $inodes[$file] = fileinode($file);
        }

        $source = $appDir . '/src/Module/AppModule.php';
        $original = filemtime($source);
        assert(is_int($original));
        try {
            // A source change must not invalidate the marker — freshness is the
            // deploy's responsibility, not a fingerprint the framework checks.
            touch($source, time() + 5);
            $injector = PackageInjector::factory($meta, 'prod-app');
        } finally {
            touch($source, $original);
        }

        $this->assertInstanceOf(CompiledInjector::class, $injector);
        foreach ($inodes as $file => $inode) {
            $this->assertSame($inode, fileinode($file), $file . ' was rewritten');
        }
    }

    public function testProdFactoryThrowsWhenMarkerPresentButScriptsBroken(): void
    {
        (new ReflectionProperty(PackageInjector::class, 'instances'))->setValue([]);
        $appDir = dirname(__DIR__) . '/Fake/fake-app';
        $meta = new Meta('FakeVendor\HelloWorld', 'prod-app', $appDir);
        $scriptDir = $meta->tmpDir . '/di';

        PackageInjector::factory($meta, 'prod-app');
        $phpScripts = glob($scriptDir . '/*.php');
        $this->assertNotFalse($phpScripts);
        foreach ($phpScripts as $file) {
            unlink($file);
        }

        // Marker present but scripts corrupted — a deploy error, not a
        // recoverable one. Must throw instead of silently rebuilding.
        try {
            PackageInjector::factory($meta, 'prod-app');
            $this->fail('A broken marker-present boot was expected to throw.');
        } catch (Throwable $e) {
            $this->assertStringContainsString('AppInterface', $e->getMessage());
        } finally {
            // Leave the script dir uncompiled so later tests start cold again.
            self::cleanProdDi($scriptDir);
        }
    }

    public function testCompileMarkerEdgeCases(): void
    {
        $missingDir = sys_get_temp_dir() . '/bear-marker-' . uniqid('', true);
        $this->assertFalse(CompileMarker::exists($missingDir));

        $dir = sys_get_temp_dir() . '/bear-marker-' . uniqid('', true);
        @mkdir($dir, 0777, true);
        try {
            $this->assertFalse(CompileMarker::exists($dir));
            CompileMarker::write($dir);
            $this->assertTrue(CompileMarker::exists($dir));
        } finally {
            @unlink(CompileMarker::path($dir));
            @rmdir($dir);
        }
    }

    /** A marker that cannot be persisted makes every later boot recompile, so it must not be swallowed. */
    public function testCompileMarkerWriteFailsLoudlyWhenNotWritable(): void
    {
        $missingDir = sys_get_temp_dir() . '/bear-marker-missing-' . uniqid('', true);
        $this->expectException(DirectoryNotWritableException::class);
        CompileMarker::write($missingDir);
    }
}

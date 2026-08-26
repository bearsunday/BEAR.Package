<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\AppMeta\Meta;
use BEAR\Package\Exception\WriteDirNotAbsoluteException;
use BEAR\Package\Module;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function dirname;
use function hash;
use function putenv;
use function str_replace;
use function sys_get_temp_dir;

class ReadOnlyAppModuleTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('FAKE_READONLY_TMP');
        putenv('FAKE_READONLY_LOG');
    }

    public function testRefusesARelativeTmpDir(): void
    {
        $this->expectException(WriteDirNotAbsoluteException::class);

        new ReadOnlyAppModule('var/tmp');
    }

    public function testRefusesARelativeLogDir(): void
    {
        $this->expectException(WriteDirNotAbsoluteException::class);

        new ReadOnlyAppModule('/data/app/tmp', 'var/log');
    }

    /** A UNC path names its own root: absolute, though no slash or drive letter starts it. */
    public function testAcceptsAWindowsUncPath(): void
    {
        $this->expectNotToPerformAssertions();

        new ReadOnlyAppModule('\\\\server\\share\\tmp', '\\\\server\\share\\log');
    }

    public function testAcceptsAWindowsDrivePath(): void
    {
        $this->expectNotToPerformAssertions();

        new ReadOnlyAppModule('C:\\data\\app\\tmp', 'C:/data/app/log');
    }

    public function testTheApplicationsInstallWins(): void
    {
        $tmpDir = sys_get_temp_dir() . '/bear-readonly-tmp';
        $logDir = sys_get_temp_dir() . '/bear-readonly-log';
        putenv('FAKE_READONLY_TMP=' . $tmpDir);
        putenv('FAKE_READONLY_LOG=' . $logDir);

        $meta = $this->bootMeta();

        $this->assertSame($tmpDir, $meta->tmpDir);
        $this->assertSame($logDir, $meta->logDir);
    }

    public function testNothingNamedFallsToTheMachineTemp(): void
    {
        $compiled = $this->compiledMeta();

        $meta = $this->bootMeta();

        $this->assertSame(
            str_replace('\\', '/', sys_get_temp_dir())
            . '/FakeVendor/HelloWorld/' . hash('xxh128', $compiled->appDir) . '/var/tmp/readonly-app',
            $meta->tmpDir,
        );
    }

    private function bootMeta(): AbstractAppMeta
    {
        $module = (new Module())($this->compiledMeta(), 'readonly-app');

        return (new Injector($module))->getInstance(AbstractAppMeta::class);
    }

    private function compiledMeta(): Meta
    {
        return new Meta('FakeVendor\HelloWorld', 'readonly-app', dirname(__DIR__) . '/Fake/fake-app');
    }
}

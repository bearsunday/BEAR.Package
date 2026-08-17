<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharComposerUnreadableException;
use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function file_put_contents;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;

class AutoloadDirsTest extends TestCase
{
    /** @var non-empty-string */
    private string $appDir;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-autoload-' . uniqid();
        mkdir($this->appDir, 0777, true);
    }

    protected function tearDown(): void
    {
        deleteFiles($this->appDir);
        @rmdir($this->appDir);
    }

    public function testEveryKeyThatNamesAPath(): void
    {
        $this->composer('{
            "autoload": {
                "psr-4": {"My\\\\App\\\\": "src/", "My\\\\Lib\\\\": ["lib/one", "lib/two"], "My\\\\Rel\\\\": "./rel"},
                "psr-0": {"Legacy_": "legacy/"},
                "classmap": ["src-fixed/Injector/"],
                "files": ["var/conf/datadog/instrumentation.php"],
                "exclude-from-classmap": ["vendor/bear/package/src/Injector/PackageInjector.php"]
            }
        }');
        $this->assertSame(
            ['src', 'lib', 'rel', 'legacy', 'src-fixed', 'var', 'vendor'],
            AutoloadDirs::of($this->appDir),
        );
    }

    /** Composer accepts both spellings for the tree's own root, and neither names a directory. */
    public function testPathsThatNameNoDirectoryOfTheTree(): void
    {
        $this->composer('{"autoload": {"psr-4": {"My\\\\App\\\\": "", "Root\\\\": "."}, "classmap": ["../shared/src"]}}');

        $this->assertSame([], AutoloadDirs::of($this->appDir));
    }

    public function testApplicationThatAutoloadsNothing(): void
    {
        $this->composer('{"name": "my/app"}');

        $this->assertSame([], AutoloadDirs::of($this->appDir));
    }

    public function testManifestThatIsNotThere(): void
    {
        $this->expectException(PharComposerUnreadableException::class);
        AutoloadDirs::of($this->appDir);
    }

    public function testManifestThatIsNotJson(): void
    {
        $this->composer('{"autoload":');

        $this->expectException(PharComposerUnreadableException::class);
        AutoloadDirs::of($this->appDir);
    }

    public function testManifestThatIsNotAnObject(): void
    {
        $this->composer('"my/app"');

        $this->expectException(PharComposerUnreadableException::class);
        AutoloadDirs::of($this->appDir);
    }

    public function testAutoloadThatIsNotAnObject(): void
    {
        $this->composer('{"autoload": "src/"}');

        $this->expectException(PharComposerUnreadableException::class);
        AutoloadDirs::of($this->appDir);
    }

    public function testPathThatIsNotAString(): void
    {
        $this->composer('{"autoload": {"classmap": [3]}}');

        $this->expectException(PharComposerUnreadableException::class);
        AutoloadDirs::of($this->appDir);
    }

    private function composer(string $json): void
    {
        file_put_contents($this->appDir . '/composer.json', $json);
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use ArrayObject;
use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Module\Import\ImportApp;
use Iterator;
use PHPUnit\Framework\TestCase;
use SplFileInfo;

use function assert;
use function basename;
use function BEAR\Package\deleteFiles;
use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_quote;
use function preg_replace;
use function realpath;
use function rmdir;
use function sort;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;

use const DIRECTORY_SEPARATOR;

class PharManifestTest extends TestCase
{
    /** @var non-empty-string */
    private string $appDir;

    /** @var non-empty-string */
    private string $writeDir;

    /** @var ArrayObject<int, string> what files() left out for being links */
    private ArrayObject $symlinked;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-manifest-' . uniqid();
        $this->writeDir = sys_get_temp_dir() . '/bear-write-' . uniqid();
        $this->symlinked = new ArrayObject();
    }

    protected function tearDown(): void
    {
        deleteFiles($this->appDir);
        @rmdir($this->appDir);
        deleteFiles($this->writeDir);
        @rmdir($this->writeDir);
    }

    public function testRootsShipTheApplicationAndItsImports(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', $this->writeDir . '/' . str_replace('\\', '/', $appName) . '/app/tmp', $this->writeDir);

        $roots = PharManifest::roots($this->appDir, [new ImportApp('foo', $appName, 'app')]);

        $real = $this->norm((string) realpath($this->appDir));
        $this->assertSame([
            $real => $real . '/var/build',
            $real . '/import' => $real . '/import/var/build',
        ], $roots);
    }

    public function testUnrelatedApplicationTreeIsIgnored(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->marker($this->appDir . '/legacy-app', 'old-app', '/var/www/legacy/var/tmp/old-app');

        $real = $this->norm((string) realpath($this->appDir));
        $this->assertSame([$real => $real . '/var/build'], PharManifest::roots($this->appDir, []));
    }

    public function testUncompiledApplication(): void
    {
        $this->expectException(PharNotCompiledException::class);
        PharManifest::roots($this->appDir, []);
    }

    /** Scripts that write inside the tree cannot work once the tree is a read-only archive. */
    public function testApplicationWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->appDir . '/var/tmp/prod-app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, []);
    }

    /** A tmpDir spelled through a symlinked var/ resolves outside, but the boot uses the spelling. */
    public function testSymlinkedVarDoesNotHideWritingIntoTheArchive(): void
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('a symlinked var/ is a POSIX deployment shape');
        }

        mkdir($this->appDir, 0777, true);
        mkdir($this->writeDir . '/real-var', 0777, true);
        symlink($this->writeDir . '/real-var', $this->appDir . '/var');
        mkdir($this->appDir . '/var/build/di', 0777, true);
        CompileMarker::write($this->appDir . '/var/build/di', 'My\App', 'prod-app', $this->appDir . '/var/tmp/prod-app', null);

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, []);
    }

    /** Compiled for a write directory of its own, so the host's boot would not find these scripts. */
    public function testImportWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', $this->appDir . '/import/var/tmp/app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, [new ImportApp('foo', $appName, 'app')]);
    }

    /** The scripts must write where the declaration that boots them derives, or the boot recompiles. */
    public function testImportCompiledOutsideTheHostWriteDir(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', '/somewhere/else/' . str_replace('\\', '/', $appName) . '/app/tmp', '/somewhere/else');

        $this->expectException(PharWriteDirMismatchException::class);
        PharManifest::roots($this->appDir, [new ImportApp('foo', $appName, 'app')]);
    }

    public function testImportOutsideTheTree(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);

        $this->expectException(PharImportOutsideTreeException::class);
        PharManifest::roots($this->appDir, [new ImportApp('foo', 'Import\HelloWorld', 'app')]);
    }

    public function testFilesShipTheNamedDirectoriesAndTheBuild(): void
    {
        $scriptDir = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($scriptDir . '/Fake_App-.php', "<?php\n");
        file_put_contents($scriptDir . '/compile.lock', 'noise');
        $this->tree([
            'src/App.php' => "<?php\n",
            'public/index.php' => "<?php\n",
            'bin/app.php' => "<?php\n",
            'vendor/autoload.php' => "<?php\n",
            'vendor/app.phar' => 'the archive being written',
            'var/sql/user_item.sql' => 'SELECT 1',
            'var/conf/aura.route.php' => "<?php\n",
            'var/json_schema/user.json' => '{}',
            'var/templates/index.html.twig' => 'hi',
            // Not named, so not carried - code an application keeps outside src/ included.
            'src-fixed/Injector/PackageInjector.php' => "<?php\n",
            'vendor-bin/tools/vendor/phpstan.php' => "<?php\n",
            'build/coverage/index.html' => '<html></html>',
            'docs/a.md' => '# a',
            'tests/AppTest.php' => "<?php\n",
            'legacy/.env.local' => 'SECRET=3',
            '.github/workflows/ci.yml' => 'on: push',
            // Nothing directly at the root.
            'autoload.php' => "<?php\n",
            'preload.php' => "<?php\n",
            'app.phar' => 'the archive of an earlier run',
            'composer.json' => '{}',
            'env.json' => '{"SECRET": 3}',
            '.env' => 'SECRET=1',
            '.env.production' => 'SECRET=2',
            'var/log/app.log' => 'log',
            // A compile step writes beside the DI scripts, and the boot reads it from there.
            'var/build/qiq/index.php' => "<?php\n",
            // Scripts left where the previous layout put them: var/tmp stays unshipped.
            'var/tmp/other-app/di/Fake_Other-.php' => "<?php\n",
        ]);
        $roots = PharManifest::roots($this->appDir, []);
        $appDir = $this->resolved();

        // An output in a directory that ships: only the manifest's own exclusion keeps it out.
        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/vendor/app.phar', 'public/index.php', $this->symlinked));

        $this->assertSame([
            'bin/app.php',
            'public/index.php',
            'src/App.php',
            'var/build/di/' . CompileMarker::FILENAME,
            'var/build/di/Fake_App-.php',
            'var/build/qiq/index.php',
            'var/conf/aura.route.php',
            'var/json_schema/user.json',
            'var/sql/user_item.sql',
            'var/templates/index.html.twig',
            'vendor/autoload.php',
        ], $shipped);
        $this->assertSame(['build', 'docs', 'legacy', 'src-fixed', 'tests', 'vendor-bin'], PharManifest::notPacked($appDir, $roots, 'public/index.php'));
    }

    /** modules/ is not a named directory: it ships only as far as the application inside it. */
    public function testFilesShipEachApplicationsOwnBuild(): void
    {
        $host = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($host . '/Fake_App-.php', "<?php\n");
        $appName = $this->importApp('modules/import');
        $import = $this->marker($this->appDir . '/modules/import', 'app', $this->writeDir . '/' . str_replace('\\', '/', $appName) . '/app/tmp', $this->writeDir);
        file_put_contents($import . '/Fake_Import-.php', "<?php\n");
        $this->tree(['modules/sibling/secrets/keys.txt' => 'PRIVATE']);

        $roots = PharManifest::roots($this->appDir, [new ImportApp('foo', $appName, 'app')]);
        $appDir = $this->resolved();

        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/app.phar', 'public/index.php', $this->symlinked));

        $this->assertSame([
            'modules/import/src/Module/AppModule.php',
            'modules/import/var/build/di/' . CompileMarker::FILENAME,
            'modules/import/var/build/di/Fake_Import-.php',
            'var/build/di/' . CompileMarker::FILENAME,
            'var/build/di/Fake_App-.php',
        ], $shipped);
        $this->assertSame([], PharManifest::notPacked($appDir, $roots, 'public/index.php'));
    }

    public function testFilesShipTheDirectoryHoldingTheEntry(): void
    {
        $scriptDir = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($scriptDir . '/Fake_App-.php', "<?php\n");
        $this->tree([
            'bootstrap/admin.php' => "<?php\n",
            'bootstrap/batch.php' => "<?php\n",
            'public-cms/index.php' => "<?php\n",
        ]);
        $roots = PharManifest::roots($this->appDir, []);
        $appDir = $this->resolved();

        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/app.phar', 'bootstrap/admin.php', $this->symlinked));

        $this->assertSame([
            'bootstrap/admin.php',
            'bootstrap/batch.php',
            'var/build/di/' . CompileMarker::FILENAME,
            'var/build/di/Fake_App-.php',
        ], $shipped);
        // One pack names one entry, so the other document root is left behind and said so.
        $this->assertSame(['public-cms'], PharManifest::notPacked($appDir, $roots, 'bootstrap/admin.php'));
    }

    /** A link the archive would not have carried anyway is left behind like any other directory. */
    public function testSymlinkedDirectoryOutsideTheAllowList(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->tree([
            'public/index.php' => "<?php\n",
            'docs/alps-doc/index.html' => '<html></html>',
        ]);
        if (! @symlink($this->appDir . '/docs/alps-doc', $this->appDir . '/alps-doc')) {
            $this->markTestSkipped('this platform does not let the test user create a symlink');
        }

        $roots = PharManifest::roots($this->appDir, []);
        $appDir = $this->resolved();

        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/app.phar', 'public/index.php', $this->symlinked));

        $this->assertSame(['public/index.php', 'var/build/di/' . CompileMarker::FILENAME], $shipped);
        $this->assertSame([], PharManifest::symlinkedDirs($this->symlinked), 'nothing was lost: it was not going to ship');
        $this->assertSame(['alps-doc', 'docs'], PharManifest::notPacked($appDir, $roots, 'public/index.php'));
    }

    /** A link where the archive expected code: packing what it points at would ship the build machine's tree. */
    public function testSymlinkedDirectoryThatWouldHaveShipped(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->tree([
            'public/index.php' => "<?php\n",
            'vendor/autoload.php' => "<?php\n",
            'src/Lib/Helper.php' => "<?php\n",
        ]);
        mkdir($this->writeDir . '/outside-package/src', 0777, true);
        file_put_contents($this->writeDir . '/outside-package/src/Outside.php', "<?php\n");
        $links = @symlink($this->writeDir . '/outside-package', $this->appDir . '/vendor/outside')
            && @symlink($this->appDir . '/src/Lib', $this->appDir . '/vendor/inside');
        if (! $links) {
            $this->markTestSkipped('this platform does not let the test user create a symlink');
        }

        $roots = PharManifest::roots($this->appDir, []);
        $appDir = $this->resolved();

        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/app.phar', 'public/index.php', $this->symlinked));

        $this->assertSame([
            'public/index.php',
            'src/Lib/Helper.php',
            'var/build/di/' . CompileMarker::FILENAME,
            'vendor/autoload.php',
        ], $shipped);
        $this->assertSame(['vendor/inside', 'vendor/outside'], PharManifest::symlinkedDirs($this->symlinked));
        $this->assertSame([], PharManifest::notPacked($appDir, $roots, 'public/index.php'));
    }

    /** @param array<string, string> $files path relative to appDir => contents */
    private function tree(array $files): void
    {
        foreach ($files as $relative => $contents) {
            $file = $this->appDir . '/' . $relative;
            $dir = dirname($file);
            ! is_dir($dir) && mkdir($dir, 0777, true);
            file_put_contents($file, $contents);
        }
    }

    /** @return non-empty-string the form roots() speaks */
    private function resolved(): string
    {
        $real = realpath($this->appDir);
        assert($real !== false);
        /** @var non-empty-string $real psalm's realpath stub says string; phpstan's already says non-empty */

        return $real;
    }

    /**
     * Cut at the fixture directory's own name: Windows spells the same root as both
     * RUNNER~1 and runneradmin, so no prefix string matches every form of it.
     *
     * @param Iterator<SplFileInfo> $files
     *
     * @return list<string> sorted, relative to appDir
     */
    private function relativePaths(Iterator $files): array
    {
        $cut = '#^.*/' . preg_quote(basename($this->appDir), '#') . '/#';
        $paths = [];
        foreach ($files as $file) {
            $paths[] = (string) preg_replace($cut, '', str_replace('\\', '/', $file->getPathname()));
        }

        sort($paths);

        return $paths;
    }

    /**
     * An application inside the tree, autoloadable so ImportApp can resolve its directory.
     *
     * @return non-empty-string application name
     */
    private function importApp(string $relative): string
    {
        $appName = 'TempImport' . str_replace('.', '', uniqid('', true));
        $dir = $this->appDir . '/' . $relative;
        mkdir($dir . '/src/Module', 0777, true);
        $file = $dir . '/src/Module/AppModule.php';
        file_put_contents($file, sprintf("<?php\n\nnamespace %s\\Module;\n\nclass AppModule\n{\n}\n", $appName));
        spl_autoload_register(static function (string $class) use ($appName, $file): void {
            if ($class !== $appName . '\Module\AppModule') {
                return;
            }

            require $file;
        });

        return $appName;
    }

    /**
     * @param non-empty-string $appDir
     * @param non-empty-string $context
     * @param non-empty-string $tmpDir
     *
     * @return non-empty-string the created script dir
     */
    private function marker(string $appDir, string $context, string $tmpDir, string|null $writeDir = null): string
    {
        $scriptDir = $appDir . '/var/build/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, 'My\App', $context, $tmpDir, $writeDir);

        $real = realpath($scriptDir);
        assert($real !== false);
        /** @var non-empty-string $real */

        return $this->norm($real);
    }

    /** @return non-empty-string forward-slashed, the form PharManifest speaks */
    private function norm(string $path): string
    {
        assert($path !== '');

        return str_replace('\\', '/', $path);
    }
}

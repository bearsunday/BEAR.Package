<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharSymlinkedDirectoryException;
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

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-manifest-' . uniqid();
        $this->writeDir = sys_get_temp_dir() . '/bear-write-' . uniqid();
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
        $host = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $import = $this->marker($this->appDir . '/import', 'app', $this->writeDir . '/' . str_replace('\\', '/', $appName) . '/app/tmp', $this->writeDir);

        $roots = PharManifest::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app')]);

        $real = $this->norm((string) realpath($this->appDir));
        $this->assertSame([$real => $host, $real . '/import' => $import], $roots);
    }

    public function testUnrelatedApplicationTreeIsIgnored(): void
    {
        $host = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->marker($this->appDir . '/legacy-app', 'old-app', '/var/www/legacy/var/tmp/old-app');

        $this->assertSame([$this->norm((string) realpath($this->appDir)) => $host], PharManifest::roots($this->appDir, 'prod-app', []));
    }

    public function testUncompiledApplication(): void
    {
        $this->expectException(PharNotCompiledException::class);
        PharManifest::roots($this->appDir, 'prod-app', []);
    }

    /** Scripts that write inside the tree cannot work once the tree is a read-only archive. */
    public function testApplicationWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->appDir . '/var/tmp/prod-app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, 'prod-app', []);
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
        mkdir($this->appDir . '/var/tmp/prod-app/di', 0777, true);
        CompileMarker::write($this->appDir . '/var/tmp/prod-app/di', 'My\App', 'prod-app', $this->appDir . '/var/tmp/prod-app', null);

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, 'prod-app', []);
    }

    /** Compiled for a write directory of its own, so the host's boot would not find these scripts. */
    public function testImportWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', $this->appDir . '/import/var/tmp/app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharManifest::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app')]);
    }

    /** The scripts must write where the declaration that boots them derives, or the boot recompiles. */
    public function testImportCompiledOutsideTheHostWriteDir(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', '/somewhere/else/' . str_replace('\\', '/', $appName) . '/app/tmp', '/somewhere/else');

        $this->expectException(PharWriteDirMismatchException::class);
        PharManifest::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app')]);
    }

    public function testImportOutsideTheTree(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);

        $this->expectException(PharImportOutsideTreeException::class);
        PharManifest::roots($this->appDir, 'prod-app', [new ImportApp('foo', 'Import\HelloWorld', 'app')]);
    }

    public function testFilesShipTheTreeAndTheScriptsButNotWhatARunWrote(): void
    {
        $scriptDir = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($scriptDir . '/Fake_App-.php', "<?php\n");
        file_put_contents($scriptDir . '/compile.lock', 'noise');
        mkdir($this->appDir . '/src', 0777, true);
        file_put_contents($this->appDir . '/src/App.php', "<?php\n");
        file_put_contents($this->appDir . '/autoload.php', "<?php\n");
        file_put_contents($this->appDir . '/preload.php', "<?php\n");
        file_put_contents($this->appDir . '/composer.json', '{}');
        mkdir($this->appDir . '/.github/workflows', 0777, true);
        file_put_contents($this->appDir . '/.github/workflows/ci.yml', 'on: push');
        file_put_contents($this->appDir . '/.env', 'SECRET=1');
        file_put_contents($this->appDir . '/.env.production', 'SECRET=2');
        file_put_contents($this->appDir . '/env.json', '{"SECRET": 3}');
        mkdir($this->appDir . '/legacy', 0777, true);
        file_put_contents($this->appDir . '/legacy/.env.local', 'SECRET=3');
        mkdir($this->appDir . '/var/sql', 0777, true);
        file_put_contents($this->appDir . '/var/sql/user_item.sql', 'SELECT 1');
        mkdir($this->appDir . '/var/conf', 0777, true);
        file_put_contents($this->appDir . '/var/conf/aura.route.php', "<?php\n");
        mkdir($this->appDir . '/var/log', 0777, true);
        file_put_contents($this->appDir . '/var/log/app.log', 'log');
        mkdir($this->appDir . '/var/build', 0777, true);
        file_put_contents($this->appDir . '/var/build/old.phar', 'an earlier archive');
        mkdir($this->appDir . '/var/tmp/other-app/di', 0777, true);
        file_put_contents($this->appDir . '/var/tmp/other-app/di/Fake_Other-.php', "<?php\n");
        mkdir($this->appDir . '/tests', 0777, true);
        file_put_contents($this->appDir . '/tests/AppTest.php', "<?php\n");
        mkdir($this->appDir . '/build', 0777, true);
        file_put_contents($this->appDir . '/build/app.phar', 'the archive being written');
        $roots = PharManifest::roots($this->appDir, 'prod-app', []);
        $appDir = $this->resolved();

        // An output inside the tree, but outside var/: the archive must not pack itself.
        $shipped = $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/build/app.phar'));

        $this->assertSame([
            'src/App.php',
            'var/conf/aura.route.php',
            'var/sql/user_item.sql',
            'var/tmp/prod-app/di/' . CompileMarker::FILENAME,
            'var/tmp/prod-app/di/Fake_App-.php',
        ], $shipped);
    }

    public function testSymlinkedDirectoryInTheTree(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        mkdir($this->writeDir . '/linked', 0777, true);
        if (! @symlink($this->writeDir . '/linked', $this->appDir . '/vendor')) {
            $this->markTestSkipped('this platform does not let the test user create a symlink');
        }

        $roots = PharManifest::roots($this->appDir, 'prod-app', []);
        $appDir = $this->resolved();

        $this->expectException(PharSymlinkedDirectoryException::class);
        $this->relativePaths(PharManifest::files($appDir, $roots, $appDir . '/var/build/prod-app.phar'));
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
        $scriptDir = $appDir . '/var/tmp/' . $context . '/di';
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

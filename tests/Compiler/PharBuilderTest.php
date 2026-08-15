<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportOutsideTreeException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharWriteDirMismatchException;
use BEAR\Package\Exception\PharWritesInsideArchiveException;
use BEAR\Package\Injector\CompileMarker;
use BEAR\Package\Module\Import\ImportApp;
use PHPUnit\Framework\TestCase;

use function assert;
use function BEAR\Package\deleteFiles;
use function dirname;
use function escapeshellarg;
use function exec;
use function file_exists;
use function file_put_contents;
use function implode;
use function is_dir;
use function mkdir;
use function realpath;
use function rmdir;
use function spl_autoload_register;
use function sprintf;
use function str_replace;
use function symlink;
use function sys_get_temp_dir;
use function uniqid;
use function var_export;

use const DIRECTORY_SEPARATOR;
use const PHP_BINARY;

class PharBuilderTest extends TestCase
{
    /** @var non-empty-string */
    private string $appDir;

    /** @var non-empty-string */
    private string $writeDir;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-pharbuild-' . uniqid();
        $this->writeDir = '/tmp/bear-write-' . uniqid();
    }

    protected function tearDown(): void
    {
        deleteFiles($this->appDir);
        @rmdir($this->appDir);
        deleteFiles($this->writeDir);
        @rmdir($this->writeDir);
    }

    /** The application and every application it declares as an import ship their scripts. */
    public function testRootsShipTheApplicationAndItsImports(): void
    {
        $host = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp');
        $appName = $this->importApp('import');
        $import = $this->marker($this->appDir . '/import', 'app', $this->writeDir . '/' . str_replace('\\', '/', $appName) . '/app/tmp');

        $roots = PharBuilder::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app', $this->writeDir)]);

        $real = $this->norm((string) realpath($this->appDir));
        $this->assertSame([$real => $host, $real . '/import' => $import], $roots);
    }

    /** An unrelated application tree in the same repository is not consulted at all. */
    public function testUnrelatedApplicationTreeIsIgnored(): void
    {
        $host = $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp');
        $this->marker($this->appDir . '/legacy-app', 'old-app', '/var/www/legacy/var/tmp/old-app');

        $this->assertSame([$this->norm((string) realpath($this->appDir)) => $host], PharBuilder::roots($this->appDir, 'prod-app', []));
    }

    public function testUncompiledApplication(): void
    {
        $this->expectException(PharNotCompiledException::class);
        PharBuilder::roots($this->appDir, 'prod-app', []);
    }

    /** Scripts that write inside the tree cannot work once the tree is a read-only archive. */
    public function testApplicationWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->appDir . '/var/tmp/prod-app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharBuilder::roots($this->appDir, 'prod-app', []);
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
        CompileMarker::write($this->appDir . '/var/tmp/prod-app/di', 'My\App', 'prod-app', $this->appDir . '/var/tmp/prod-app');

        $this->expectException(PharWritesInsideArchiveException::class);
        PharBuilder::roots($this->appDir, 'prod-app', []);
    }

    /** An import whose AppModule never read APP_WRITE_DIR writes under its own tree, inside the archive. */
    public function testImportWritingIntoTheArchive(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp');
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', $this->appDir . '/import/var/tmp/app');

        $this->expectException(PharWritesInsideArchiveException::class);
        $this->expectExceptionMessageMatches('#/import" were compiled to write to#');
        PharBuilder::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app')]);
    }

    /** The scripts must write where the declaration that boots them derives, or the boot recompiles. */
    public function testImportCompiledForAnotherWriteDirThanDeclared(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp');
        $appName = $this->importApp('import');
        $this->marker($this->appDir . '/import', 'app', '/somewhere/else/' . str_replace('\\', '/', $appName) . '/app/tmp');

        $this->expectException(PharWriteDirMismatchException::class);
        PharBuilder::roots($this->appDir, 'prod-app', [new ImportApp('foo', $appName, 'app', $this->writeDir)]);
    }

    /** An import outside the tree being packed cannot ship at all. */
    public function testImportOutsideTheTree(): void
    {
        $this->marker($this->appDir, 'prod-app', $this->writeDir . '/My/App/prod-app/tmp');

        $this->expectException(PharImportOutsideTreeException::class);
        $this->expectExceptionMessageMatches('#import-app" lies outside#');
        PharBuilder::roots($this->appDir, 'prod-app', [new ImportApp('foo', 'Import\HelloWorld', 'app')]);
    }

    /**
     * The whole build through the worker, the way Compiler::phar() runs it: what ships
     * ships, and what must not - .env anywhere, logs, build noise - does not.
     */
    public function testBuildsArchiveThroughWorker(): void
    {
        $tmpDir = $this->writeDir . '/My/App/prod-app/tmp';
        $scriptDir = $this->marker($this->appDir, 'prod-app', $tmpDir);
        file_put_contents($scriptDir . '/Fake_App-.php', "<?php\nreturn null;\n");
        file_put_contents($scriptDir . '/compile.lock', 'noise');
        mkdir($this->appDir . '/public', 0777, true);
        file_put_contents($this->appDir . '/public/index.php', "<?php\n");
        mkdir($this->appDir . '/src', 0777, true);
        file_put_contents($this->appDir . '/src/App.php', "<?php\n");
        file_put_contents($this->appDir . '/.env', 'SECRET=1');
        mkdir($this->appDir . '/legacy/var/log', 0777, true);
        file_put_contents($this->appDir . '/legacy/.env', 'SECRET=2');
        mkdir($this->appDir . '/var/log', 0777, true);
        file_put_contents($this->appDir . '/var/log/app.log', 'log');
        mkdir($this->appDir . '/vendor', 0777, true);
        file_put_contents($this->appDir . '/vendor/autoload.php', sprintf("<?php\nreturn require %s;\n", var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true)));

        $command = sprintf(
            '%s -d phar.readonly=0 %s prod-app %s public/index.php %s 2>&1',
            escapeshellarg(PHP_BINARY),
            escapeshellarg(dirname(__DIR__, 2) . '/bin/phar-worker.php'),
            escapeshellarg($this->appDir),
            escapeshellarg(''),
        );
        exec($command, $lines, $exitCode);
        $output = implode("\n", $lines);

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Boot: APP_WRITE_DIR=' . $this->writeDir, $output);
        $phar = 'phar://' . realpath($this->appDir . '/var/build/prod-app.phar');
        $this->assertTrue(file_exists($phar . '/src/App.php'));
        $this->assertTrue(file_exists($phar . '/var/tmp/prod-app/di/Fake_App-.php'));
        $this->assertTrue(file_exists($phar . '/var/tmp/prod-app/di/' . CompileMarker::FILENAME));
        $this->assertFalse(file_exists($phar . '/.env'));
        $this->assertFalse(file_exists($phar . '/legacy/.env'), 'a .env outside the application root must not ship either');
        $this->assertFalse(file_exists($phar . '/var/log/app.log'));
        $this->assertFalse(file_exists($phar . '/var/tmp/prod-app/di/compile.lock'));
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
    private function marker(string $appDir, string $context, string $tmpDir): string
    {
        $scriptDir = $appDir . '/var/tmp/' . $context . '/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, 'My\App', $context, $tmpDir);

        $real = realpath($scriptDir);
        assert($real !== false);
        /** @var non-empty-string $real */

        return $this->norm($real);
    }

    /** @return non-empty-string forward-slashed, the form PharBuilder speaks */
    private function norm(string $path): string
    {
        assert($path !== '');

        return str_replace('\\', '/', $path);
    }
}

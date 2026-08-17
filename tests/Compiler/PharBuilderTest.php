<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharEntryNotFoundException;
use BEAR\Package\Exception\PharNotCompiledException;
use BEAR\Package\Exception\PharStaleOutputException;
use BEAR\Package\Injector\CompileMarker;
use Phar;
use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function chdir;
use function dirname;
use function escapeshellarg;
use function exec;
use function file_exists;
use function file_put_contents;
use function getcwd;
use function implode;
use function is_dir;
use function mkdir;
use function realpath;
use function rmdir;
use function sprintf;
use function str_replace;
use function sys_get_temp_dir;
use function uniqid;
use function var_export;

use const PHP_BINARY;

/**
 * Writing an archive takes -d phar.readonly=0, so PharBuilder is exercised through the
 * worker, the way Compiler::phar() runs it. Its decisions are PharManifest's.
 *
 * @see PharManifestTest
 */
class PharBuilderTest extends TestCase
{
    /** @var non-empty-string */
    private string $appDir;

    /** @var non-empty-string */
    private string $writeDir;

    protected function setUp(): void
    {
        $this->appDir = sys_get_temp_dir() . '/bear-pharbuild-' . uniqid();
        $this->writeDir = sys_get_temp_dir() . '/bear-write-' . uniqid();
    }

    protected function tearDown(): void
    {
        deleteFiles($this->appDir);
        @rmdir($this->appDir);
        deleteFiles($this->writeDir);
        @rmdir($this->writeDir);
    }

    public function testPacksWhatTheManifestSelected(): void
    {
        $scriptDir = $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($scriptDir . '/Fake_App-.php', "<?php\nreturn null;\n");
        file_put_contents($scriptDir . '/compile.lock', 'noise');
        mkdir($this->appDir . '/src', 0777, true);
        file_put_contents($this->appDir . '/src/App.php', "<?php\n");
        file_put_contents($this->appDir . '/.env', 'SECRET=1');
        file_put_contents($this->appDir . '/env.json', '{"SECRET": 2}');
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Phar: ' . realpath($this->appDir) . '/var/build/prod-app.phar', $output);
        $this->assertStringContainsString('Writes: ' . $this->writeDir, $output);
        $phar = 'phar://' . realpath($this->appDir . '/var/build/prod-app.phar');
        $this->assertTrue(file_exists($phar . '/public/index.php'), 'the stub requires this entry');
        $this->assertTrue(file_exists($phar . '/src/App.php'));
        $this->assertTrue(file_exists($phar . '/var/build/prod-app/di/Fake_App-.php'));
        $this->assertTrue(file_exists($phar . '/var/build/prod-app/di/' . CompileMarker::FILENAME), 'the boot reads the marker from the archive');
        $this->assertFalse(file_exists($phar . '/.env'));
        $this->assertFalse(file_exists($phar . '/env.json'), 'a secret at the root ships under any name');
        $this->assertFalse(file_exists($phar . '/var/build/prod-app/di/compile.lock'));
    }

    public function testEntryThatIsNotOnDisk(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);

        $this->expectException(PharEntryNotFoundException::class);
        (new PharBuilder())('prod-app', $this->appDir, 'public/nowhere.php');
    }

    /** An entry the manifest drops would leave a stub requiring a path the archive lacks. */
    public function testEntryThatCannotShip(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        file_put_contents($this->appDir . '/.env', 'SECRET=1');
        $this->vendor();

        [$exitCode, $output] = $this->worker('.env');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('PharEntryNotPackedException: ' . realpath($this->appDir) . '/.env', $output);
    }

    public function testTreeThatWasNeverCompiled(): void
    {
        $this->entry();

        $this->expectException(PharNotCompiledException::class);
        (new PharBuilder())('prod-app', $this->appDir, 'public/index.php');
    }

    /** Packing into whatever survives at the output path would ship the last build's entries too. */
    public function testPreviousArchiveThatCannotBeRemoved(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->entry();
        mkdir($this->appDir . '/var/build/prod-app.phar', 0777, true);

        $this->expectException(PharStaleOutputException::class);
        (new PharBuilder())('prod-app', $this->appDir, 'public/index.php');
    }

    /** A compile that named its own tmp directory was placed under no base, so there is none to print. */
    public function testReportWithoutAWriteDirectory(): void
    {
        $this->marker($this->writeDir . '/somewhere/of/its/own');
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Phar: ', $output);
        $this->assertStringNotContainsString('Writes: ', $output);
    }

    public function testWorkerUnderAReadOnlyPharIni(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php', readOnly: true);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('start this worker with -d phar.readonly=0', $output);
    }

    /** A relative output is resolved before anything else looks at it. */
    public function testRelativeOutputIsResolvedBeforeTheStaleCheck(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->entry();
        mkdir($this->appDir . '/var/build/prod-app.phar', 0777, true);

        $cwd = getcwd();
        chdir($this->appDir);
        try {
            (new PharBuilder())('prod-app', $this->appDir, 'public/index.php', 'var/build/prod-app.phar');
            $this->fail('a directory at the output path is a stale output');
        } catch (PharStaleOutputException $e) {
            $this->assertStringContainsString($this->norm((string) realpath($this->appDir)), $this->norm($e->getMessage()));
        } finally {
            chdir($cwd !== false ? $cwd : dirname(__DIR__, 2));
        }
    }

    /** An output named relative to the tree must not end up inside the archive being written. */
    public function testRelativeOutputUnderTheApplicationTree(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp', $this->writeDir);
        $this->entry();
        $this->vendor();

        $cwd = getcwd();
        chdir($this->appDir);
        try {
            [$exitCode, $output] = $this->worker('public/index.php', 'var/build/prod-app.phar');
        } finally {
            chdir($cwd !== false ? $cwd : dirname(__DIR__, 2));
        }

        $this->assertSame(0, $exitCode, $output);
        $built = realpath($this->appDir) . '/var/build/prod-app.phar';
        $this->assertStringContainsString($this->norm($built), $this->norm($output));
        $phar = new Phar($built);
        $this->assertFalse(isset($phar['var/build/prod-app.phar']), 'the archive packed itself');
    }

    /** The worker runs under the deploy's ini, where assert() is off. */
    public function testWorkerWithoutAnApplication(): void
    {
        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('holds no vendor/autoload.php', $output);
    }

    public function testWorkerWithoutItsArguments(): void
    {
        exec(sprintf('%s %s 2>&1', escapeshellarg(PHP_BINARY), escapeshellarg(self::workerScript())), $lines, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('usage:', implode("\n", $lines));
    }

    /** @return array{int, string} exit code and merged output of one worker run */
    private function worker(string $entry, string $output = '', bool $readOnly = false): array
    {
        $command = sprintf(
            '%s -d phar.readonly=%d %s prod-app %s %s %s 2>&1',
            escapeshellarg(PHP_BINARY),
            (int) $readOnly,
            escapeshellarg(self::workerScript()),
            escapeshellarg($this->appDir),
            escapeshellarg($entry),
            escapeshellarg($output),
        );
        exec($command, $lines, $exitCode);

        return [$exitCode, implode("\n", $lines)];
    }

    private static function workerScript(): string
    {
        return dirname(__DIR__, 2) . '/bin/phar-worker.php';
    }

    /**
     * @param non-empty-string $tmpDir
     *
     * @return non-empty-string the script directory the marker was written to
     */
    private function marker(string $tmpDir, string|null $writeDir = null): string
    {
        $scriptDir = $this->appDir . '/var/build/prod-app/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, 'My\App', 'prod-app', $tmpDir, $writeDir);

        return $scriptDir;
    }

    /** Windows spells realpath() with backslashes, and the worker prints what it was given. */
    private function norm(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    private function entry(): void
    {
        mkdir($this->appDir . '/public', 0777, true);
        file_put_contents($this->appDir . '/public/index.php', "<?php\n");
    }

    /** An autoloader the worker can require: the fixture is not a Composer project. */
    private function vendor(): void
    {
        mkdir($this->appDir . '/vendor', 0777, true);
        file_put_contents(
            $this->appDir . '/vendor/autoload.php',
            sprintf("<?php\nreturn require %s;\n", var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true)),
        );
    }
}

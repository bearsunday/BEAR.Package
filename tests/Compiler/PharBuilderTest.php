<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Injector\CompileMarker;
use PHPUnit\Framework\TestCase;

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
use function sprintf;
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
        $scriptDir = $this->marker($this->writeDir . '/My/App/prod-app/tmp');
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
        $this->assertStringContainsString('Boot: APP_WRITE_DIR=' . $this->writeDir, $output);
        $phar = 'phar://' . realpath($this->appDir . '/var/build/prod-app.phar');
        $this->assertTrue(file_exists($phar . '/public/index.php'), 'the stub requires this entry');
        $this->assertTrue(file_exists($phar . '/src/App.php'));
        $this->assertTrue(file_exists($phar . '/var/tmp/prod-app/di/Fake_App-.php'));
        $this->assertTrue(file_exists($phar . '/var/tmp/prod-app/di/' . CompileMarker::FILENAME), 'the boot reads the marker from the archive');
        $this->assertFalse(file_exists($phar . '/.env'));
        $this->assertFalse(file_exists($phar . '/env.json'), 'a secret at the root ships under any name');
        $this->assertFalse(file_exists($phar . '/var/tmp/prod-app/di/compile.lock'));
    }

    public function testEntryThatIsNotOnDisk(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp');
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/nowhere.php');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('does not exist', $output);
    }

    /** An entry the manifest drops would leave a stub requiring a path the archive lacks. */
    public function testEntryThatCannotShip(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp');
        file_put_contents($this->appDir . '/.env', 'SECRET=1');
        $this->vendor();

        [$exitCode, $output] = $this->worker('.env');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('is not in the archive', $output);
    }

    public function testTreeThatWasNeverCompiled(): void
    {
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('No compiled DI scripts', $output);
    }

    /** Packing into whatever survives at the output path would ship the last build's entries too. */
    public function testPreviousArchiveThatCannotBeRemoved(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp');
        $this->entry();
        $this->vendor();
        mkdir($this->appDir . '/var/build/prod-app.phar', 0777, true);

        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Cannot remove the previous archive', $output);
    }

    /** A tmpDir that does not follow the writeDir convention names no write directory to print. */
    public function testReportWithoutAWriteDirectory(): void
    {
        $this->marker($this->writeDir . '/somewhere/of/its/own');
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php');

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('Phar: ', $output);
        $this->assertStringNotContainsString('Boot: ', $output);
    }

    public function testWorkerUnderAReadOnlyPharIni(): void
    {
        $this->marker($this->writeDir . '/My/App/prod-app/tmp');
        $this->entry();
        $this->vendor();

        [$exitCode, $output] = $this->worker('public/index.php', readOnly: true);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('start this worker with -d phar.readonly=0', $output);
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
    private function marker(string $tmpDir): string
    {
        $scriptDir = $this->appDir . '/var/tmp/prod-app/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, 'My\App', 'prod-app', $tmpDir);

        return $scriptDir;
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

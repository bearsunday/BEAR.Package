<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Compiler;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Injector\CompileMarker;
use PHPUnit\Framework\TestCase;

use function BEAR\Package\deleteFiles;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function preg_match_all;
use function rmdir;
use function sprintf;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;
use function var_export;

class PreloadRecorderTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/../Fake/fake-app';
    private const CONTEXT = 'prod-app';

    /**
     * A boot without current scripts compiles on demand, and recording that would write the
     * compiler into preload.php - the error the recorder exists to remove. It must not guess.
     */
    public function testRefusesATreeWithoutACurrentBuild(): void
    {
        // A tree of its own: what other tests compiled into the fixture must not decide this.
        $appDir = sys_get_temp_dir() . '/bear-preload-recorder-' . uniqid('', true);
        mkdir($appDir . '/vendor', 0777, true);
        file_put_contents(
            $appDir . '/vendor/autoload.php',
            sprintf("<?php\nreturn require %s;\n", var_export(dirname(__DIR__, 2) . '/vendor/autoload.php', true)),
        );

        try {
            $this->expectException(PreloadRecordException::class);
            $this->expectExceptionMessage('needs the compiled scripts');
            (new PreloadRecorder())(self::APP_NAME, self::CONTEXT, $appDir);
        } finally {
            deleteFiles($appDir);
            rmdir($appDir);
        }
    }

    /**
     * A context that assembles the container per request has no compiled boot to record: the
     * recording would be of Ray.Di building the graph, which is what preload must never hold.
     */
    public function testRefusesAContextThatAssemblesPerRequest(): void
    {
        $context = 'app';
        $scriptDir = self::APP_DIR . '/var/build/' . $context . '/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, self::APP_NAME, $context);

        try {
            $this->expectException(PreloadRecordException::class);
            $this->expectExceptionMessage('assembles the container on each request');
            (new PreloadRecorder())(self::APP_NAME, $context, self::APP_DIR);
        } finally {
            // A marker for a per-request context is this fixture's fiction: left behind, it
            // tells every later boot of "app" that there is a build to boot from.
            @unlink(CompileMarker::path($scriptDir));
        }
    }

    /**
     * The recorder is what the worker runs. Here it runs in-process, which is exactly what it
     * must not do in a compile: the boot path is already in memory, so nothing autoloads and
     * the list comes out short. What this proves is the writing and the shape - the content
     * contract belongs to CompilerTest, which compiles in a clean child process.
     */
    public function testWritesAGuardedPreloadForTheCompiledArtifact(): void
    {
        (new Compiler(self::APP_NAME, self::CONTEXT, self::APP_DIR))->compile();

        $preload = (new PreloadRecorder())(self::APP_NAME, self::CONTEXT, self::APP_DIR);

        $this->assertFileExists($preload);
        $contents = (string) file_get_contents($preload);
        $this->assertStringContainsString("if (in_array(PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {", $contents);
        $this->assertStringContainsString("require __DIR__ . '/vendor/autoload.php';", $contents);
        $this->assertGreaterThan(
            0,
            preg_match_all('/^ {4}opcache_compile_file\(/m', $contents),
            'The DI scripts the boot loaded are compiled, never required',
        );
    }
}

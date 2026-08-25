<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Injector\CompileMarker;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function is_file;
use function mkdir;
use function preg_match_all;
use function unlink;

class PreloadRecorderTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/../Fake/fake-app';
    private const CONTEXT = 'prod-app';

    /**
     * A boot with no build to read compiles on demand, and recording that would write the
     * compiler into preload.php - the error the recorder exists to remove. It must not guess.
     */
    public function testRefusesWhenThereIsNoCompiledBuildToRecord(): void
    {
        // The fixture is shared, so a marker that was there has to be there afterwards
        $marker = CompileMarker::path(self::APP_DIR . '/var/build/' . self::CONTEXT . '/di');
        $recorded = is_file($marker) ? (string) file_get_contents($marker) : null;
        @unlink($marker);

        try {
            $this->expectException(PreloadRecordException::class);
            (new PreloadRecorder())(self::APP_NAME, self::CONTEXT, self::APP_DIR);
        } finally {
            $recorded === null || file_put_contents($marker, $recorded);
        }
    }

    /**
     * A context that assembles the container per request has no compiled boot to record: the
     * recording would be of Ray.Di building the graph, which is what preload must never hold.
     */
    public function testRefusesAContextThatAssemblesPerRequest(): void
    {
        $context = 'app';
        $meta = new Meta(self::APP_NAME, $context, self::APP_DIR);
        $scriptDir = self::APP_DIR . '/var/build/' . $context . '/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, self::APP_NAME, $context, $meta->tmpDir);

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

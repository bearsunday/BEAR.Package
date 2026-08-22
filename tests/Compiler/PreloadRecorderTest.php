<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\Meta;
use BEAR\Package\Compiler;
use BEAR\Package\Exception\PreloadRecordException;
use BEAR\Package\Injector\CompileMarker;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function is_dir;
use function mkdir;
use function preg_match_all;
use function sys_get_temp_dir;
use function uniqid;

class PreloadRecorderTest extends TestCase
{
    private const APP_NAME = 'FakeVendor\HelloWorld';
    private const APP_DIR = __DIR__ . '/../Fake/fake-app';
    private const CONTEXT = 'prod-app';

    /**
     * A boot without current scripts compiles on demand, and recording that would write the
     * compiler into preload.php - the error the recorder exists to remove. It must not guess.
     */
    public function testRefusesScriptsCompiledForAnotherWritableDirectory(): void
    {
        $writeDir = sys_get_temp_dir() . '/bear-preload-recorder-' . uniqid('', true);
        $this->expectException(PreloadRecordException::class);
        $this->expectExceptionMessage('needs the compiled scripts');
        (new PreloadRecorder())(self::APP_NAME, self::CONTEXT, self::APP_DIR, $writeDir);
    }

    /**
     * A context that assembles the container per request has no compiled boot to record: the
     * recording would be of Ray.Di building the graph, which is what preload must never hold.
     */
    public function testRefusesAContextThatAssemblesPerRequest(): void
    {
        $context = 'app';
        $meta = Meta::create(self::APP_NAME, $context, self::APP_DIR, null);
        $scriptDir = self::APP_DIR . '/var/build/' . $context . '/di';
        ! is_dir($scriptDir) && mkdir($scriptDir, 0777, true);
        CompileMarker::write($scriptDir, self::APP_NAME, $context, $meta->tmpDir);

        $this->expectException(PreloadRecordException::class);
        $this->expectExceptionMessage('assembles the container on each request');
        (new PreloadRecorder())(self::APP_NAME, $context, self::APP_DIR, null);
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

        $preload = (new PreloadRecorder())(self::APP_NAME, self::CONTEXT, self::APP_DIR, null);

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

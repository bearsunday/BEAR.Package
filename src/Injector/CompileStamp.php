<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filemtime;
use function glob;
use function hash;
use function implode;
use function is_dir;
use function max;

/**
 * Compile stamp for deciding whether AOT DI scripts can be reused.
 *
 * @see https://github.com/bearsunday/BEAR.Package/issues/483
 */
final class CompileStamp
{
    private const STAMP = '.compile-stamp';

    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    public static function stampPath(string $scriptDir): string
    {
        return $scriptDir . '/' . self::STAMP;
    }

    public static function of(AbstractAppMeta $meta): string
    {
        $parts = [
            'src:' . self::maxMtime($meta->appDir . '/src'),
            'lock:' . self::fileStamp($meta->appDir . '/composer.lock'),
        ];
        $envFiles = glob($meta->appDir . '/.env*');
        if ($envFiles !== false) {
            foreach ($envFiles as $envFile) {
                $parts[] = 'env:' . self::fileStamp($envFile);
            }
        }

        return hash('xxh128', implode("\n", $parts));
    }

    public static function matches(AbstractAppMeta $meta, string $scriptDir): bool
    {
        $stampFile = self::stampPath($scriptDir);
        if (! file_exists($stampFile) || ! is_dir($scriptDir)) {
            return false;
        }

        $saved = @file_get_contents($stampFile);
        if ($saved === false || $saved === '') {
            return false;
        }

        return $saved === self::of($meta);
    }

    public static function write(AbstractAppMeta $meta, string $scriptDir): void
    {
        @file_put_contents(self::stampPath($scriptDir), self::of($meta));
    }

    private static function fileStamp(string $path): string
    {
        if (! file_exists($path)) {
            return '0';
        }

        return $path . ':' . (string) filemtime($path);
    }

    private static function maxMtime(string $dir): string
    {
        if (! is_dir($dir)) {
            return '0';
        }

        $times = [0];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY,
        );
        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            $times[] = (int) $file->getMTime();
        }

        return (string) max($times);
    }
}

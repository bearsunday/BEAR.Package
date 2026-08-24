<?php

declare(strict_types=1);

namespace BEAR\Package\Provide\Cache;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Exception\DirectoryNotWritableException;
use Override;
use Ray\Di\ProviderInterface;

use function is_dir;
use function is_writable;
use function mkdir;

/** @implements ProviderInterface<string> */
final class CacheDirProvider implements ProviderInterface
{
    private const CACHE_DIRNAME = '/cache';

    public function __construct(private AbstractAppMeta $appMeta)
    {
    }

    #[Override]
    public function get(): string
    {
        $cacheDir = $this->appMeta->tmpDir . self::CACHE_DIRNAME;
        // Created only when absent, so a directory that exists and cannot be written is
        // refused rather than mistaken for one a concurrent process just made.
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        if (! is_writable($cacheDir)) {
            throw new DirectoryNotWritableException($cacheDir);
        }

        return $cacheDir;
    }
}

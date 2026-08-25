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
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0777, true);
        }

        // Asked again rather than trusting mkdir(): false is a lost race, a file, or a refusal
        if (! is_dir($cacheDir) || ! is_writable($cacheDir)) {
            throw new DirectoryNotWritableException($cacheDir);
        }

        return $cacheDir;
    }
}

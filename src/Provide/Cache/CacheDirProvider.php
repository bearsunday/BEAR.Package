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
        if (is_writable($cacheDir)) {
            return $cacheDir;
        }

        if (! @mkdir($cacheDir, 0777, true) && ! is_dir($cacheDir)) {
            // @codeCoverageIgnoreStart
            throw new DirectoryNotWritableException($cacheDir);
            // @codeCoverageIgnoreEnd
        }

        return $cacheDir;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;

use function dirname;
use function str_replace;
use function str_starts_with;

/**
 * The base an application writes under, derived from its tmpDir.
 *
 * Meta::create() lays it out as {base}/{Vendor}/{Project}/{context}/tmp, so the base is
 * four levels up; a tmpDir under appDir means the default var/ layout and no separate
 * base. Derived because app-meta no longer carries the base as a property.
 *
 * @psalm-internal BEAR\Package
 */
final class WriteBase
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /** @return non-empty-string|null */
    public static function of(AbstractAppMeta $meta): string|null
    {
        $tmpDir = str_replace('\\', '/', $meta->tmpDir);
        $appDir = str_replace('\\', '/', $meta->appDir);
        if (str_starts_with($tmpDir, $appDir . '/')) {
            return null;
        }

        /** @var non-empty-string $base */
        $base = dirname($tmpDir, 4);

        return $base;
    }
}

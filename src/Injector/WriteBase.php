<?php

declare(strict_types=1);

namespace BEAR\Package\Injector;

use BEAR\AppMeta\AbstractAppMeta;

use function preg_match;
use function preg_quote;
use function str_replace;
use function str_starts_with;

/**
 * The base an application writes under, derived from its tmpDir.
 *
 * Meta::create() lays it out as {base}/{Vendor}/{Project}/{context}/tmp; a tmpDir under
 * appDir means the default var/ layout and no separate base. Derived because app-meta no
 * longer carries the base as a property.
 *
 * A Meta built with a directly-specified tmpDir matches no layout: return null then, the
 * same answer the property read gave. Anything else would publish a guess into the compile
 * marker.
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

        $name = preg_quote(str_replace('\\', '/', $meta->name), '#');
        if (! preg_match('#^(.+)/' . $name . '/[^/]+/tmp$#', $tmpDir, $m)) {
            return null;
        }

        /** @var non-empty-string $base the capture is .+ so it cannot be empty */
        $base = $m[1];

        return $base;
    }
}

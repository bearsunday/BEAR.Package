<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\AppMeta\AbstractAppMeta;
use BEAR\Package\Types;
use Ray\Compiler\CompiledInjector;

use function assert;

/**
 * Where a compiled container writes, asked of the container itself.
 *
 * @psalm-import-type ScriptDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class CompiledWriteDir
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param ScriptDir $scriptDir
     *
     * @return TmpDir
     */
    public static function of(string $scriptDir): string
    {
        /** @psalm-suppress InvalidArgument the compiled container binds it through AppMetaProvider */
        $meta = (new CompiledInjector($scriptDir))->getInstance(AbstractAppMeta::class);
        assert($meta instanceof AbstractAppMeta);

        return $meta->tmpDir;
    }
}

<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Package\Types;
use BEAR\Resource\Annotation\ImportAppConfig;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Exception\Unbound;

use function is_array;

/**
 * The applications a compiled container imports, asked of the container itself.
 *
 * Unbound says only that no script holds the binding, so reading it as "imports nothing"
 * needs the caller's compile marker: proof that the rest of the directory is there.
 *
 * @psalm-import-type ScriptDir from Types
 */
final class ImportedApps
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param ScriptDir $scriptDir compiled DI scripts whose compile marker the caller has read
     *
     * @return list<ImportApp>
     *
     * @throws PharImportsUnreadableException
     */
    public static function of(string $scriptDir): array
    {
        try {
            /**
             * @psalm-suppress ArgumentTypeCoercion the compiled container holds the binding under this name
             * @var mixed $config
             */
            $config = (new CompiledInjector($scriptDir))->getInstance('', ImportAppConfig::class);
        } catch (Unbound) {
            return [];  // ImportAppModule was never installed - the marker says the rest is here
        }

        if (! is_array($config)) {
            throw new PharImportsUnreadableException($scriptDir);
        }

        $imports = [];
        /** @var mixed $import */
        foreach ($config as $import) {
            if (! $import instanceof ImportApp) {
                throw new PharImportsUnreadableException($scriptDir);
            }

            $imports[] = $import;
        }

        return $imports;
    }
}

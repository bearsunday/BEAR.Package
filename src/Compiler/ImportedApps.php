<?php

declare(strict_types=1);

namespace BEAR\Package\Compiler;

use BEAR\Package\Exception\PharImportsUnreadableException;
use BEAR\Package\Module\Import\ImportApp;
use BEAR\Resource\Annotation\ImportAppConfig;
use Ray\Compiler\CompiledInjector;
use Ray\Compiler\Exception\Unbound;

use function is_array;

/**
 * The applications a compiled container imports, asked of the container itself.
 *
 * No binding is the only failure that means "imports nothing": anything else is a
 * container that cannot say, and packing it would ship an archive missing an application.
 */
final class ImportedApps
{
    /** @codeCoverageIgnore */
    private function __construct()
    {
    }

    /**
     * @param non-empty-string $scriptDir compiled DI scripts of the importing application
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
            return [];
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

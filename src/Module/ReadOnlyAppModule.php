<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Types;
use Override;
use Ray\Di\AbstractModule;

/**
 * Install it from the application's own ProdModule. Both directories are named rather than
 * worked out: a value resolved while compiling would be the build machine's, and the archive
 * would carry it.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class ReadOnlyAppModule extends AbstractModule
{
    /**
     * @param string $tmpDir absolute path for what a run may discard
     * @param string $logDir absolute path for what it may not
     */
    public function __construct(
        private string $tmpDir,
        private string $logDir,
        AbstractModule|null $module = null,
    ) {
        parent::__construct($module);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function configure(): void
    {
        $this->bind(WriteDirs::class)->toInstance(new WriteDirs($this->tmpDir, $this->logDir));
    }
}

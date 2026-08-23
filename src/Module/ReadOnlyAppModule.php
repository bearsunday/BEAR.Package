<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\AppMeta\Types;
use Override;
use Ray\Di\AbstractModule;

/**
 * Say where an application writes, for a tree it cannot write to.
 *
 * Install it from the application's own ProdModule. Both directories are named rather than
 * worked out: the archive carries whatever this declares, and a value resolved while
 * compiling would be the build machine's, which the deployment has no reason to have.
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

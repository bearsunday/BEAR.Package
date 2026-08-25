<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Types;
use Override;
use Ray\Di\AbstractModule;

/**
 * Install it from the application's own ProdModule.
 *
 * A named directory is compiled in as given, so it must be one every machine that boots the
 * build can write to. An omitted one is answered by the machine, under its temp directory,
 * keeping the shape the application would have used inside its own tree.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class ReadOnlyAppModule extends AbstractModule
{
    /**
     * @param TmpDir|null $tmpDir absolute path for what a run may discard
     * @param LogDir|null $logDir absolute path for what it may not
     */
    public function __construct(
        private string|null $tmpDir = null,
        private string|null $logDir = null,
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

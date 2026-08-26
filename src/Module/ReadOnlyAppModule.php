<?php

declare(strict_types=1);

namespace BEAR\Package\Module;

use BEAR\Package\Exception\WriteDirNotAbsoluteException;
use BEAR\Package\Types;
use Override;
use Ray\Di\AbstractModule;

use function preg_match;

/**
 * Install from the application's ProdModule to name where the application writes.
 *
 * A named directory is compiled in as given: every machine that boots the build must be able
 * to write to it, and every context that reaches this install shares it (`prod-app` and
 * `prod-hal-app` alike). An omitted one falls to the booting machine's temp directory.
 *
 * @psalm-import-type LogDir from Types
 * @psalm-import-type TmpDir from Types
 */
final class ReadOnlyAppModule extends AbstractModule
{
    /**
     * @param TmpDir|null $tmpDir absolute path
     * @param LogDir|null $logDir absolute path
     *
     * @throws WriteDirNotAbsoluteException
     */
    public function __construct(
        private string|null $tmpDir = null,
        private string|null $logDir = null,
        AbstractModule|null $module = null,
    ) {
        $this->refuseRelative($tmpDir);
        $this->refuseRelative($logDir);

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

    /** @throws WriteDirNotAbsoluteException */
    private function refuseRelative(string|null $dir): void
    {
        if ($dir === null || preg_match('#^(/|[A-Za-z]:[\\\\/])#', $dir) === 1) {
            return;
        }

        throw new WriteDirNotAbsoluteException($dir);
    }
}
